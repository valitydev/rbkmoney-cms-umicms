<?php

namespace src\Helpers;

use ConnectionPool;
use DateTime;

class Logger
{

    public function __construct()
    {
        require_once dirname(__DIR__) . '/settings.php';
    }

    /**
     * @param Log $log
     *
     * @return void
     */
    public function saveLog(Log $log)
    {
        if (!realpath(LOG_FILE_PATH)) {
            mkdir(LOG_FILE_PATH, 0777, true);
        }

        $date = new DateTime();

        $pathToLogFile = LOG_FILE_PATH . '/' . LOG_FILE_NAME;

        foreach ($log->toArray() as $field => $value) {
            file_put_contents(
                $pathToLogFile,
                "{$date->format('Y.m.d H:i:s')}: $field => $value\r\n",
                FILE_APPEND
            );
        }

        file_put_contents($pathToLogFile, "\r\n", FILE_APPEND);
    }

    /**
     * @return string
     */
    public function getLog()
    {
        $pathToLogFile = LOG_FILE_PATH . '/' . LOG_FILE_NAME;

        if (!realpath($pathToLogFile)) {
            return '';
        }

        return file_get_contents($pathToLogFile);
    }

    /**
     * @return bool
     */
    public function deleteLog()
    {
        $pathToLogFile = LOG_FILE_PATH . '/' . LOG_FILE_NAME;

        if (realpath($pathToLogFile)) {
            $file = fopen($pathToLogFile, 'w');

            if (false === $file) {
                return false;
            }

            fclose($file);
        }

        return true;
    }

    public function downloadLog()
    {
        $pathToLogFile = LOG_FILE_PATH . '/' . LOG_FILE_NAME;

        if (file_exists($pathToLogFile)) {
            if (ob_get_level()) {
                ob_end_clean();
            }
            header('Content-Description: File Transfer');
            header('Content-Type: application/octet-stream');
            header('Content-Disposition: attachment; filename=' . basename($pathToLogFile));
            header('Content-Transfer-Encoding: binary');
            header('Expires: 0');
            header('Cache-Control: must-revalidate');
            header('Pragma: public');

            $connection = ConnectionPool::getInstance()->getConnection();
            $cmsVersion = $connection->queryResult("SELECT * FROM `cms_reg` WHERE `var` = 'system_version' ORDER BY `id` DESC limit 1");
            $version = $cmsVersion->getIterator()->current();
            $sql = $connection->queryResult('SELECT VERSION()');
            $sqlVersion = $sql->getIterator()->current();

            print LOG_FILE_COMMENT;
            print "\r\n=============\r\n";
            print "CMS: UMI.CMS {$version['val']}";
            print "\r\n=============\r\n";
            print "MySQL version: {$sqlVersion['VERSION()']}";
            print "\r\n=============\r\n";

            foreach ($this->phpinfo2array() as $key => $item) {
                print "$key";

                if (is_array($item)) {
                    array_walk_recursive($item, function($value, $key) {
                        print "\r\n$key -> $value";
                    });
                } else {
                    print " $item";
                }

                print "\r\n\r\n\r\n\r\n\r\n";
            }

            print "=============\r\n";

            readfile($pathToLogFile);

            exit();
        }
    }

    /**
     * @author Calin S. http://php.net/manual/function.phpinfo.php#117961
     *
     * @return array
     */
    private function phpinfo2array()
    {
        $entitiesToUtf8 = function($input) {
            // http://php.net/manual/en/function.html-entity-decode.php#104617
            return preg_replace_callback("/(&#[0-9]+;)/", function($m) {
                return mb_convert_encoding($m[1], "UTF-8", "HTML-ENTITIES");
            }, $input);
        };
        $plainText = function($input) use ($entitiesToUtf8) {
            return trim(html_entity_decode($entitiesToUtf8(strip_tags($input))));
        };
        $titlePlainText = function($input) use ($plainText) {
            return '# ' . $plainText($input);
        };

        ob_start();
        phpinfo(-1);

        $phpinfo = array('phpinfo' => array());

        // Strip everything after the <h1>Configuration</h1> tag (other h1's)
        if (!preg_match('#(.*<h1[^>]*>\s*Configuration.*)<h1#s', ob_get_clean(), $matches)) {
            return array();
        }

        $input = $matches[1];
        $matches = array();

        if (preg_match_all(
            '#(?:<h2.*?>(?:<a.*?>)?(.*?)(?:<\/a>)?<\/h2>)|' .
            '(?:<tr.*?><t[hd].*?>(.*?)\s*</t[hd]>(?:<t[hd].*?>(.*?)\s*</t[hd]>(?:<t[hd].*?>(.*?)\s*</t[hd]>)?)?</tr>)#s',
            $input,
            $matches,
            PREG_SET_ORDER
        )) {
            foreach ($matches as $match) {
                $fn = strpos($match[0], '<th') === false ? $plainText : $titlePlainText;
                if (strlen($match[1])) {
                    $phpinfo[$match[1]] = array();
                } elseif (isset($match[3])) {
                    $keys1 = array_keys($phpinfo);
                    $phpinfo[end($keys1)][$fn($match[2])] = isset($match[4]) ? array(
                        $fn($match[3]),
                        $fn($match[4])
                    ) : $fn($match[3]);
                } else {
                    $keys1 = array_keys($phpinfo);
                    $phpinfo[end($keys1)][] = $fn($match[2]);
                }
            }
        }

        return $phpinfo;
    }

}
