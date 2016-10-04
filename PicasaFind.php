<?php

class PicasaFind
{
    private $_path = '';
    private $_pi = null;  // PicasaIni object
    private $_ip = null;  // FileProcessor object
    private $_log = '';

    public function __construct ($path, $pi, $ip, $log)
    {
        $this->_path = realpath ($path);
        $this->_pi = $pi;
        $this->_ip = $ip;
        $this->_log = $log;
    }

    public function run ()
    {
        $count = 0;

        $dirs = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($this->_path, RecursiveDirectoryIterator::SKIP_DOTS),
            RecursiveIteratorIterator::SELF_FIRST,
            RecursiveIteratorIterator::CATCH_GET_CHILD // Ignore "Permission denied"
        );

        foreach($dirs as $dir => $object)
        {
            $count++;
            if (!is_dir ($dir))
            {
                continue;
            }

            if (!preg_match ('#\d\d\d\d/\d\d\d\d#', $dir))
            {
                $this->log_msg ("NO YYYY/MMDD FORMAT - $dir");
                continue;
            }

            $ini_file = '';
            if (file_exists ("$dir/Picasa.ini"))
            {
                $ini_file = "$dir/Picasa.ini";
            }
            elseif (file_exists ("$dir/.picasa.ini"))
            {
                $ini_file = "$dir/.picasa.ini";
            }

            if (!$ini_file)
            {
                $this->log_msg ("NO PICASA.INI       - $dir");
                continue;
            }

            $this->log_msg ("PROCESSING          - $ini_file");
            $this->_pi->process ($ini_file);
            $this->_ip->process ($dir);
        }

        print "$count files/dirs examined...\n";
    }

    private function log_msg ($msg)
    {
        error_log ("$msg\n", 3, $this->_log);
    }
}
