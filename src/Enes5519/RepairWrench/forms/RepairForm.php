<?php

namespace Enes5519\RepairWrench\forms;

use Enes5519\RepairWrench\RepairWrench;
use pocketmine\form\Form;
use pocketmine\form\MenuForm;
use pocketmine\form\MenuOption;
use pocketmine\item\Item;
use pocketmine\Player;
use pocketmine\utils\TextFormat;

class RepairForm extends MenuForm{

    /** @var RepairWrench */
    private $api;
    /** @var Item[] */
    protected $itemData = [];

    public function __construct(Player $player){
        $this->api = RepairWrench::getAPI();
        parent::__construct("§eRepair Wrench", str_repeat(" ", 15)."§aYour Money: §f".$this->api->getEconomyAPI()->getMonetaryUnit().$this->api->getEconomyAPI()->myMoney($player).str_repeat("\n", 2), $this->getMenuOptions($player));
    }

    private function getMenuOptions(Player $player) : array{
        $this->itemData = [];
        $config = $this->api->getConfig()->getAll()['repairable-items'];

        $options = [];
        foreach($player->getInventory()->getContents(false) as $item){
            if($item->getDamage() > 0 && isset($config[$item->getId()]) && empty($options[$item->getId()])){
                $data = $config[$item->getId()];
                $options[$item->getId()] = new MenuOption("§0".$data['name']." : ".$this->api->getEconomyAPI()->getMonetaryUnit().$data['price'], RepairWrench::createIconFromConfigData($data));
                $this->itemData[] = $item;
            }
        }

        return $options;
    }

    public function onSubmit(Player $player) : ?Form{
        $index = $this->getSelectedOptionIndex();
        $item = $this->itemData[$index];
        $config = $this->api->getConfig()->getAll()['repairable-items'][$item->getId()];

        $price = $config['price'];
        $myMoney = $this->api->getEconomyAPI()->myMoney($player);
        if($myMoney < $price){
            $player->sendMessage(TextFormat::RED . "You don't have enough money to repair item. You are missing ".$this->api->getEconomyAPI()->getMonetaryUnit().($price - $myMoney).".");
            return null;
        }

        // TODO : Add permission for free and repair

        $this->api->getEconomyAPI()->reduceMoney($player, $price, true);

        $index = $player->getInventory()->first($item);
        $player->getInventory()->setItem($index, (clone $item)->setDamage(0));

        $player->sendMessage(TextFormat::GREEN . "Item successfully repaired!");

        return null;
    }

}