<?php

// https://gist.github.com/fbuchinger/1073823

class PicasaIni
{
    private $_data_dir = '';
    private $_log = '';

    private $_albums;

    private $_ini_file = '';
    private $_ymd = '';
    private $_dir = '';

    public function __construct ($data_dir, $log)
    {
        $this->_data_dir = $data_dir;
        $this->_log = $log;
    }

    public function process ($ini_file)
    {
        $this->_ini_file = $ini_file;
        $fp = fopen ($ini_file, 'r');

        if (!preg_match ('#(\d\d\d\d)/(\d\d)(\d\d)#', $ini_file, $matches))
        {
            $this->log_msg ("No YYYYMMDD in ini file path");
            return;
        }

        list ($junk, $y, $m, $d) = $matches;
        $this->_ymd = "$y-$m-$d";

        $pinfo = pathinfo ($ini_file);
        $this->_dir = $pinfo['dirname'];

        $this->_albums = array ();

        $section_type = '';
        $key_values = array ();
        while ($line = chop (fgets ($fp)))
        {
            if (preg_match ('#^\[#', $line))
            {
                $this->process_kv ($section_type, $key_values);
                $key_values = array ();

                if (preg_match ('#^\[Contacts#', $line))
                {
                    $section_type = 'contacts';
                }
                else if (preg_match ('#^\[\.album:#', $line))
                {
                    $section_type = 'album';
                }
                else if (preg_match ('#^\[Picasa#', $line))
                {
                    $section_type = 'picasa';
                }
                else if (preg_match ('#\[(.+\.(?:jpe?g|gif|png|tiff?))\]#i', $line, $matches))
                {
                    list ($junk, $imagepath) = $matches;
                    $section_type = 'image';
                    $key_values['_imagepath'] = $imagepath;
                }
                else if (preg_match ('#\[(encoding)#', $line))
                {
                    $section_type = '';
                }
                else
                {
                    $this->log_msg ("unknown section type: $line\n");
                    $section_type = '';
                }

                continue;
            }

            // process a key_value
            list ($key, $value) = explode ("=", $line);
            $key_values[$key] = $value;
        }
        $this->process_kv ($section_type, $key_values);
    }

    private function process_kv ($section_type, $kv)
    {
        switch ($section_type)
        {
            case 'contacts':
                // TODO
                //$this->process_contacts_kv ($kv);
                break;

            case 'album':
                $this->process_album_kv ($kv);
                break;

            case 'image':
                $this->process_image_kv ($kv);
                break;
        }
    }

    private function process_album_kv ($kv)
    {
        $name = '';
        $token = '';
        $date = '';

        foreach ($kv as $k => $v)
        {
            switch ($k)
            {
                case 'name':
                    $name = $v;
                    break;

                case 'token':
                    $token = $v;
                    break;

                case 'date':
                    $date = $v;
                    break;

                case 'default':
                    $this->log_msg ("unknown album property: $k");
                    break;
            }
        }
        if ($token)
        {
            $album_file = $this->_data_dir . '/albums/' . $token . '.meta';
            if (!file_exists ($album_file))
            {
                file_put_contents ($album_file, json_encode (array (
                    'name' => $name,
                    'token' => $token,
                    'date' => $date,
                )));
            }
        }
    }

    private function process_image_kv ($kv)
    {
        if (!isset ($kv['_imagepath']))
        {
            $this->log_msg ("imagepath not set: " . print_r ($kv, true));
            return;
        }

        if (!file_exists ($this->_dir . '/' . $kv['_imagepath']))
        {
            $this->log_msg ("image not found: " . $kv['_imagepath']);
            return;
        }

        foreach ($kv as $k => $v)
        {
            switch ($k)
            {
                case 'star':
                    if ($v == 'yes')
                    {
                        $star_file = $this->_data_dir . '/star_albums/' . $this->_ymd;
                        error_log ($this->_dir . '/' . $kv['_imagepath'] . "\n", 3, $star_file);
                    }
                    break;

                case 'faces':
                    $faces = explode (';', $kv['faces']);
                    foreach ($faces as $f)
                    {
                        list ($rect, $id) = explode (',', $f);
                        // TODO -- do something with the faces
                    }
                    break;

                case 'albums':
                    // TODO - if you're in multiple albums, are they comma-delimited?  semicolon?
                    $albums = explode (',', $v);
                    foreach ($albums as $a)
                    {
                        $album_file = $this->_data_dir . '/albums/' . $a;
                        error_log ($this->_dir . '/' . $kv['_imagepath'] . "\n", 3, $album_file);
                    }
                    break;

                case 'keywords':
                    // TODO -- handle keywords (only appears here for non-JPEG images)
                    break;

                case 'caption':
                    // TODO -- handle captions (only appears here for non-JPEG images)
                    break;

                // other keys that we don't need to worry about
                case '_imagepath':
                case 'crop':
                case 'redo':
                case 'moddate':
                case 'textactive':
                case 'width':
                case 'height':
                case 'filters':
                case 'backuphash':
                case 'rotate':  // TODO: handle rotate?
                    break;

                default:
                    if (preg_match ('#^IIDLIST_#', $k))
                    {
                        continue;
                    }
                    $this->log_msg ("unknown image property '$k'");
                    break;
            }
        }
    }

    private function log_msg ($msg)
    {
        error_log ($this->_ini_file . " - $msg\n", 3, $this->_log);
    }
}

