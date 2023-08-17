<?php
use RecursiveDirectoryIterator as RDI;
use RecursiveIteratorIterator as RII;

class Autoload{
  protected static ?RII $file_iterator = null;

  public static string $path;

  public static function loadPath(){
    self::$path = str_replace('\\', '/', __DIR__.'/');
  }

  public static function loader(string $classname){
    $dir = new RDI(self::$path, RDI::SKIP_DOTS);
    $classname = self::slash($classname);

    if(is_null(self::$file_iterator))
      self::$file_iterator = new RII($dir, RII::LEAVES_ONLY);

    foreach(static::$file_iterator as $file){
      $path = str_replace(
        [self::$path, '.php'], 
        '', 
        self::slash($file->getPathName())
      );

      if(strtolower($path) === strtolower($classname)){
        if($file->isReadable())
          include_once $file->getPathName();

        break;
      }
    }
  }

  public static function slash(string $path, string $slash = '/'){
    return str_replace($slash == '/' ? '\\' : '/', $slash, $path);
  }

  public static function getFilesFromFolder(string $folder){
    $files = [];

    foreach(self::$file_iterator as $file){
      $path = str_replace(
        [self::$path, '.php'], 
        '', 
        self::slash($file->getPathName())
      );

      if(is_int(strpos($path, $folder)))
        $files[] = $path;
    }

    return $files;
  }
}

Autoload::loadPath();
spl_autoload_register('Autoload::loader');