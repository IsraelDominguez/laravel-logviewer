<?php namespace Genetsis\LogViewer;
use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;


/**
 * Class LogViewer.
 */
class LogViewer
{

    /**
     * The log file name.
     *
     * @var string
     */
    public $file;

    /**
     * The path of log file.
     *
     * @var string
     */
    protected $filePath;

    /**
     * Start and end offset in current page.
     *
     * @var array
     */
    protected $pageOffset = [];

    /**
     * @var array
     */
    public static $levelColors = [
        'EMERGENCY' => 'default',
        'ALERT'     => 'danger',
        'CRITICAL'  => 'danger',
        'ERROR'     => 'danger',
        'WARNING'   => 'warning',
        'NOTICE'    => 'info',
        'INFO'      => 'info',
        'DEBUG'     => 'success',
    ];

    /**
     * LogViewer constructor.
     * @param null $file
     * @throws \Exception
     */
    public function __construct($file = null)
    {
        if (is_null($file)) {
            $file = $this->getLastModifiedLog();
        }

        $this->file = $file;

        $this->getFilePath();
    }

    /**
     * Get file path by giving log file name.
     *
     * @throws \Exception
     *
     * @return string
     */
    public function getFilePath()
    {
        if (!$this->filePath) {
            $path = sprintf(storage_path('logs/%s'), $this->file);

            if (!file_exists($path)) {
                throw new \Exception('log not exists!');
            }

            $this->filePath = $path;
        }

        return $this->filePath;
    }

    /**
     * Get size of log file.
     *
     * @return int
     */
    public function getFilesize()
    {
        return filesize($this->filePath);
    }

    /**
     * Get log file list in storage.
     *
     * @param int $count
     *
     * @return array
     */
    public function getLogFiles($count = 20)
    {
        $files = glob(storage_path('logs/*'));
        $files = array_combine($files, array_map('filemtime', $files));
        arsort($files);

        $files = array_map('basename', array_keys($files));

        return array_slice($files, 0, $count);
    }

    /**
     * Get the last modified log file.
     *
     * @return string
     */
    public function getLastModifiedLog()
    {
        $logs = $this->getLogFiles();

        return current($logs);
    }

    /**
     * Get previous page url.
     *
     * @return bool|string
     */
    public function getPrevPageUrl()
    {
        if ($this->pageOffset['end'] >= $this->getFilesize() - 1) {
            return false;
        }

        return route('log-viewer-file', [
            'file' => $this->file, 'offset' => $this->pageOffset['end'],
        ]);
    }

    /**
     * Get Next page url.
     *
     * @return bool|string
     */
    public function getNextPageUrl()
    {
        if ($this->pageOffset['start'] == 0) {
            return false;
        }

        return route('log-viewer-file', [
            'file' => $this->file, 'offset' => -$this->pageOffset['start'],
        ]);
    }

    /**
     * Fetch logs by giving offset.
     *
     * @param int $seek
     * @param int $lines
     * @param int $buffer
     *
     * @return array
     *
     * @see http://www.geekality.net/2011/05/28/php-tail-tackling-large-files/
     */
    public function fetch($seek = 0, $lines = 20, $buffer = 4096)
    {
        $f = fopen($this->filePath, 'rb');

        if ($seek) {
            fseek($f, abs($seek));
        } else {
            fseek($f, 0, SEEK_END);
        }

        if (fread($f, 1) != "\n") {
            $lines -= 1;
        }
        fseek($f, -1, SEEK_CUR);

        // Start reading
        if ($seek > 0) {
            $output = '';

            $this->pageOffset['start'] = ftell($f);

            while (!feof($f) && $lines >= 0) {
                $output = $output.($chunk = fread($f, $buffer));
                $lines -= substr_count($chunk, "\n[20");
            }

            $this->pageOffset['end'] = ftell($f);

            while ($lines++ < 0) {
                $strpos = strrpos($output, "\n[20") + 1;
                $_ = mb_strlen($output, '8bit') - $strpos;
                $output = substr($output, 0, $strpos);
                $this->pageOffset['end'] -= $_;
            }

        } else {
            $output = '';

            $this->pageOffset['end'] = ftell($f);

            while (ftell($f) > 0 && $lines >= 0) {
                $offset = min(ftell($f), $buffer);
                fseek($f, -$offset, SEEK_CUR);
                $output = ($chunk = fread($f, $offset)).$output;
                fseek($f, -mb_strlen($chunk, '8bit'), SEEK_CUR);
                $lines -= substr_count($chunk, "\n[20");
            }

            $this->pageOffset['start'] = ftell($f);

            while ($lines++ < 0) {
                $strpos = strpos($output, "\n[20") + 1;
                $output = substr($output, $strpos);
                $this->pageOffset['start'] += $strpos;
            }
        }

        fclose($f);

        return $this->parseLog($output);
    }


    /**
     * Delete log file
     *
     * @return bool
     * @throws FileNotFoundException
     */
    public function delete()
    {
        if ($this->filePath) {
            Log::debug($this->filePath);
            if ( ! Storage::delete($this->filePath)) {
                throw new FileNotFoundException('There was an error deleting the log.');
            }
            return true;
        }
        return false;
    }


    /**
     * Parse raw log text to array.
     *
     * @param $raw
     *
     * @return array
     */
    protected function parseLog($raw)
    {
        $logs = preg_split('/\[(\d{4}(?:-\d{2}){2} \d{2}(?::\d{2}){2})\] (\w+)\.(\w+):((?:(?!{"exception").)*)?/', trim($raw), -1, PREG_SPLIT_DELIM_CAPTURE | PREG_SPLIT_NO_EMPTY);

        foreach ($logs as $index => $log) {
            if (preg_match('/^\d{4}/', $log)) {
                break;
            } else {
                unset($logs[$index]);
            }
        }

        if (empty($logs)) {
            return [];
        }

        $parsed = [];

        foreach (array_chunk($logs, 5) as $log) {
            $parsed[] = [
                'time'  => $log[0] ?? '',
                'env'   => $log[1] ?? '',
                'level' => $log[2] ?? '',
                'info'  => $log[3] ?? '',
                'trace' => trim($log[4] ?? ''),
            ];
        }

        unset($logs);

        rsort($parsed);

        return $parsed;
    }
}