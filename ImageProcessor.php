<?php

class ImageProcessor
{
    private $_data_dir = '';
    private $_log = '';

    private $_dir = '';

    public function __construct ($data_dir, $log)
    {
        $this->_data_dir = $data_dir;
        $this->_log = $log;
    }

    public function process ($dir)
    {
        $this->_dir = $dir;
    }

    private function log_msg ($msg)
    {
        error_log ($this->_dir . " - $msg\n", 3, $this->_log);
    }
}

