<?php

namespace Enes5519\RepairWrench\lang;

use pocketmine\Player;
use pocketmine\utils\MainLogger;

class Lang{

    public const DEFAULT_LOCALE = 'en_US';

    /** @var string */
    protected $locale = self::DEFAULT_LOCALE;
    /** @var array */
    protected $lang = [];
    /** @var array */
    protected $defaultLang = [];

    public function __construct(Player $player){
        $locale = $player->getLocale();
        $this->locale = $locale;

        if(!self::loadLang($file = __DIR__."/locale/$locale.ini", $this->lang)){
            MainLogger::getLogger()->debug("Missing required language file $file");
        }

        if(!self::loadLang($file = __DIR__."/locale/".self::DEFAULT_LOCALE.".ini", $this->defaultLang)){
            MainLogger::getLogger()->error("Missing required language file $file");
        }
    }

    protected static function loadLang(string $path, array &$output){
        if(file_exists($path)){
            $output = array_map('stripcslashes', parse_ini_file($path, false, INI_SCANNER_RAW));
            return true;
        }

        return false;
    }

    public function translate(string $str, array $params = []) : string{
        $baseText = $this->get($str);
        $baseText = $this->parseTranslation($baseText !== null ? $baseText : $str);

        foreach($params as $i => $p){
            $baseText = str_replace("{%$i}", $this->parseTranslation((string) $p), $baseText);
        }

        return $baseText;
    }

    public function get(string $id){
        return isset($this->lang[$id]) ? $this->lang[$id] : (isset($this->defaultLang[$id]) ? $this->defaultLang[$id] : $id);
    }

    public function internalGet(string $id){
        return isset($this->lang[$id]) ? $this->lang[$id] : (isset($this->defaultLang[$id]) ? $this->defaultLang[$id] : null);
    }

    protected function parseTranslation(string $text) : string{
        $newString = "";

        $replaceString = null;

        $len = strlen($text);
        for($i = 0; $i < $len; ++$i){
            $c = $text{$i};
            if($replaceString !== null){
                $ord = ord($c);
                if(
                    ($ord >= 0x30 and $ord <= 0x39) // 0-9
                    or ($ord >= 0x41 and $ord <= 0x5a) // A-Z
                    or ($ord >= 0x61 and $ord <= 0x7a) or // a-z
                    $c === "." or $c === "-"
                ){
                    $replaceString .= $c;
                }else{
                    if(($t = $this->internalGet(substr($replaceString, 1))) !== null){
                        $newString .= $t;
                    }else{
                        $newString .= $replaceString;
                    }
                    $replaceString = null;

                    if($c === "%"){
                        $replaceString = $c;
                    }else{
                        $newString .= $c;
                    }
                }
            }elseif($c === "%"){
                $replaceString = $c;
            }else{
                $newString .= $c;
            }
        }

        if($replaceString !== null){
            if(($t = $this->internalGet(substr($replaceString, 1))) !== null){
                $newString .= $t;
            }else{
                $newString .= $replaceString;
            }
        }

        return $newString;
    }
}