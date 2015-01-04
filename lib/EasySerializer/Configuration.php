<?php
/*
  +---------------------------------------------------------------------------------+
  | Copyright (c) 2015 César Rodas                                                  |
  +---------------------------------------------------------------------------------+
  | Redistribution and use in source and binary forms, with or without              |
  | modification, are permitted provided that the following conditions are met:     |
  | 1. Redistributions of source code must retain the above copyright               |
  |    notice, this list of conditions and the following disclaimer.                |
  |                                                                                 |
  | 2. Redistributions in binary form must reproduce the above copyright            |
  |    notice, this list of conditions and the following disclaimer in the          |
  |    documentation and/or other materials provided with the distribution.         |
  |                                                                                 |
  | 3. All advertising materials mentioning features or use of this software        |
  |    must display the following acknowledgement:                                  |
  |    This product includes software developed by César D. Rodas.                  |
  |                                                                                 |
  | 4. Neither the name of the César D. Rodas nor the                               |
  |    names of its contributors may be used to endorse or promote products         |
  |    derived from this software without specific prior written permission.        |
  |                                                                                 |
  | THIS SOFTWARE IS PROVIDED BY CÉSAR D. RODAS ''AS IS'' AND ANY                   |
  | EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE IMPLIED       |
  | WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE          |
  | DISCLAIMED. IN NO EVENT SHALL CÉSAR D. RODAS BE LIABLE FOR ANY                  |
  | DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES      |
  | (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES;    |
  | LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND     |
  | ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT      |
  | (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE OF THIS   |
  | SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE                     |
  +---------------------------------------------------------------------------------+
  | Authors: César Rodas <crodas@php.net>                                           |
  +---------------------------------------------------------------------------------+
*/
namespace EasySerializer;

use crodas\FileUtil\File;
use WatchFiles\Watch;
use Notoj\Dir;

class Configuration
{
    protected static $loaded = array();
    protected $tmp;
    protected $dir;

    protected function load($file)
    {
        if (empty(self::$loaded[$file])) {
            self::$loaded[$file] = require $file;
        }
        return self::$loaded[$file];
    }

    public function __construct($dir = null, $tmp_file = null)
    {
        $dir = $dir ?: getcwd();
        $tmp_file = $tmp_file ?: File::generateFilePath('EasySerializer', $dir);

        $this->dir = $dir;
        $this->tmp = $tmp_file;
        $this->watch = new Watch($this->tmp . ".lock");
    }

    public function getSerializer()
    {
        if ($this->watch->hasChanged()) {
            return $this->generateSerializer();
        }
        return $this->load($this->tmp);
    }

    protected function generateSerializer()
    {
        $annDir = new Dir($this->dir);
        $annotations = $annDir->getAnnotations();
        $classes = array();
        foreach ($annotations->get('Serialize') as $obj) {
            if (!$obj->isClass()) {
                continue;
            }
            $classes[strtolower($obj['class'])] = new Serializer($obj);
            $this->watch->watchFile($obj['file']);
        }

        $this->watch->watch();

        foreach ($classes as $class) {
            $class->validate($classes);
        }

        $validator = new Validator('', '');
        $validator->setClass($annotations->get('Serialize'));
        $validator = $validator->getCode();


        $code = Templates::get('serializer')->render(compact('classes', 'validator'), true);
        File::write($this->tmp, $code);

        unset(self::$loaded[$this->tmp]);
        return $this->load($this->tmp);
    }
}
