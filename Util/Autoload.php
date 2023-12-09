<?php
namespace Util;

define('DS', DIRECTORY_SEPARATOR);

use RecursiveDirectoryIterator as RDI;
use RecursiveIteratorIterator as RII;

class Autoload{
  public static ?RII $iterator = null;
  public static string $path;

  public static function load(string $cls): void{
    self::$path = isset(self::$path) ? self::$path : __DIR__.DS.'..'.DS;

    if(is_null(self::$iterator))
      self::$iterator = new RII(new RDI(self::$path, RDI::SKIP_DOTS), RII::LEAVES_ONLY);

    foreach(self::$iterator as $file){
      $path = trim(str_replace([self::$path, '.php'], '', $file->getPathName()), '\\/');
      
      if(strtolower($path) === strtolower($cls)){
        include_once $file->getPathName();
        break;
      }
    }
  }
}

spl_autoload_register("Util\Autoload::load");