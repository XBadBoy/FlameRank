<?php namespace flame\rank;

/*
 *      _____                      _                    _____ _    _ _____
 *     |_   _|                    | |                  / ____| |  | |_   _|
 *       | |  _ ____   _____ _ __ | |_ ___  _ __ _   _| |  __| |  | | | |
 *       | | | '_ \ \ / / _ \ '_ \| __/ _ \| '__| | | | | |_ | |  | | | |
 *      _| |_| | | \ V /  __/ | | | || (_) | |  | |_| | |__| | |__| |_| |_
 *     |_____|_| |_|\_/ \___|_| |_|\__\___/|_|   \__, |\_____|\____/|_____|
 *                                                __/ |
 *                                               |___/
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Lesser General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * @author XBadBoy,iCat21
 * @Youtube Channel: Alfiangaming
 * @link http://github.com/XBadBoy
 */

use pocketmine\Server;
use pocketmine\Player;
use pocketmine\plugin\PluginBase;
use pocketmine\event\Listener;
use pocketmine\utils\Config;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\command\ConsoleCommandSender;
use jojoe77777\FormAPI\SimpleForm;
use onebone\economyapi\EconomyAPI;

class Rank extends PluginBase implements Listener {
	
	public $rankData = [];
	public function onEnable(){
		$this->getServer()->getLogger()->info("§7[§eRank Custom§7] §aEnabled!");
		$this->saveDefaultConfig();
		$this->saveResource("ranks.yml");
		$this->getServer()->getPluginManager()->registerEvents($this, $this);
		$this->rank = new Config($this->getDataFolder()."ranks.yml", Config::YAML);
		$this->eco = $this->getServer()->getPluginManager()->getPlugin("EconomyAPI");
	}
	
	public function onCommand(CommandSender $sender, Command $cmd, string $label, array $args) : bool {
        switch($cmd->getName()){
        	case "rank":
                if($sender instanceof Player){          	
                	$this->openRankUI($sender);
                }
            break;
        }
        return true;
    }
    
    public function openRankUI($player){
    	$form = new SimpleForm(function(Player $player, int $data = null){
    	    if(is_null($data)) return;
            $button = $data;
			$list = array_keys($this->rank->getAll());
			$rank = $list[$button];
	        $this->rankData[$player->getName()] = $rank;
            $money = $this->eco->myMoney($player);
            $price = $this->rank->getNested($rank.".price");
            if($money >= $price){
			    $player->sendMessage($this->getConfig()->get("message.success-buy"));
			    $this->eco->reduceMoney($player, $price);
			    $this->getServer()->dispatchCommand(new ConsoleCommandSender(), "setgroup ".$player->getName()." ".$this->rank->getNested($rank.".name"));
			    $this->getServer()->dispatchCommand(new ConsoleCommandSender(), "addgroup ".$this->rank->getNested($rank.".name"));
			    if($this->rank->getNested($rank.".give") === true){
			        $this->getServer()->dispatchCommand(new ConsoleCommandSender(), "moneygive ".$player->getName()." ".$this->rank->getNested($rank.".givemoney"));
			    }
			    $player->sendMessage("§eSuccesfully buy rank §a".$this->rank->getNested($rank.".name"));
			}else{
			    $player->sendMessage($this->getConfig()->get("message.no-money"));
			}
		});
		$form->setTitle($this->getConfig()->get("title.form"));
		foreach(array_keys($this->rank->getAll()) as $rank){
			$money = $this->eco->myMoney($player);
            $price = $this->rank->getNested($rank.".price");
            $form->setContent("§fList ".$this->getConfig()->get("title.form")." §e".count($this->rank->getAll())." §frank's in this server!\n §fSelect to buy you rank!\n");
			if($money >= $price){
				$form->addButton("§r".$this->rank->getNested($rank.".name")."\n§7Price: §a".$this->rank->getNested($rank.".price")."  §7Open to buy!");
			}else{
				$form->addButton("§r".$this->rank->getNested($rank.".name")."\n§7Price: §c".$this->rank->getNested($rank.".price")."  §7Cek your money!");
			}
		}
		$form->sendToPlayer($player);
	}
}