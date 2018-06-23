<?php

declare(strict_types=1);

namespace Enes5519\RepairWrench;

use Enes5519\RepairWrench\forms\RepairForm;
use onebone\economyapi\EconomyAPI;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\form\FormIcon;
use pocketmine\Player;
use pocketmine\plugin\PluginBase;
use pocketmine\utils\TextFormat;

class RepairWrench extends PluginBase{

    /** @var RepairWrench */
    private static $api;

    /** @var EconomyAPI */
    private $economyAPI;

    public function onLoad(){
        self::$api = $this;
    }

    public static function getAPI(): RepairWrench{
        return self::$api;
    }

    public function onEnable(){
        if(!class_exists('onebone\economyapi\EconomyAPI')){
            $this->getLogger()->error("EconomyAPI is required to use this plugin.");
            $this->setEnabled(false);
            return;
        }

        $this->economyAPI = EconomyAPI::getInstance();

        @mkdir($this->getDataFolder());
        $this->saveDefaultConfig();
    }

    public function onCommand(CommandSender $sender, Command $command, string $label, array $args) : bool{
        if(!empty($args)){
            return false;
        }

        if(!($sender instanceof Player)){
            $sender->sendMessage(TextFormat::RED . "This command must be executed as a player");
            return true;
        }

        $form = new RepairForm($sender);

        if($form->getOption(0) !== null){
            $sender->sendForm(new RepairForm($sender));
        }else{
            $sender->sendMessage($form->getLang()->translate('no-items-to-repair'));
        }

        return true;
    }

    public function getEconomyAPI() : EconomyAPI{
        return $this->economyAPI;
    }

    public static function createIconFromConfigData(array $data) : ?FormIcon{
        $check = [FormIcon::IMAGE_TYPE_URL, FormIcon::IMAGE_TYPE_PATH];

        foreach($check as $c)
            if(isset($data[$c]))
                return new FormIcon($data[$c], $c);

        return null;
    }
}