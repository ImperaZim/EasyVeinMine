<?php

declare(strict_types = 1);

namespace ImperaZim\EasyVeinMine;

use pocketmine\block\Block;
use pocketmine\block\BlockTypeIds;
use pocketmine\block\VanillaBlocks;
use pocketmine\event\Listener;
use pocketmine\event\block\BlockBreakEvent;
use pocketmine\player\Player;
use pocketmine\plugin\PluginBase;
use pocketmine\world\World;

/**
* Class VeinMine
* @package ImperaZim\EasyVeinMine
*/
final class VeinMine extends PluginBase implements Listener {
 
  /** @var int[] */
  private array $allowedBlocks = [
    BlockTypeIds::COAL_ORE,
    BlockTypeIds::IRON_ORE,
    BlockTypeIds::GOLD_ORE,
    BlockTypeIds::LAPIS_ORE,
    BlockTypeIds::DIAMOND_ORE,
    BlockTypeIds::EMERALD_ORE,
    BlockTypeIds::REDSTONE_ORE,
    BlockTypeIds::NETHER_QUARTZ_ORE
  ];

  /**
  * Called when the plugin is enabled.
  */
  protected function onEnable(): void {
    $this->getServer()->getPluginManager()->registerEvents($this, $this);
  }

  /**
  * Handles the block break event.
  * @param BlockBreakEvent $event
  */
  public function onBlockBreak(BlockBreakEvent $event): void {
    $block = $event->getBlock();
    $player = $event->getPlayer();
    if ($player->isSneaking() && in_array($block->getTypeId(), $this->allowedBlocks, true)) {
      $this->veinMine($block, $player);
    }
  }

  /**
  * Performs the vein mining operation.
  * @param Block $startBlock
  * @param Player $player
  * @param array $checkedBlocks
  */
  private function veinMine(Block $startBlock, Player $player, array &$checkedBlocks = []): void {
    $blockId = $startBlock->getTypeId();
    $blocksToBreak = [$startBlock];
    $checkedBlocks[] = $startBlock->getPosition()->__toString();
    while (!empty($blocksToBreak)) {
      $currentBlock = array_pop($blocksToBreak);
      foreach ($currentBlock->getAllSides() as $adjacentBlock) {
        if ($adjacentBlock->getTypeId() === $blockId && !in_array($adjacentBlock->getPosition()->__toString(), $checkedBlocks, true)) {
          $blocksToBreak[] = $adjacentBlock;
          $checkedBlocks[] = $adjacentBlock->getPosition()->__toString();
        }
      }
      $world = $player->getWorld();
      if ($player->isCreative()) {
        $world->setBlock($currentBlock->getPosition(), VanillaBlocks::AIR());
      } else {
        $currentBlock->onBreak($player->getInventory()->getItemInHand(), $player);
      }
    }
  }
}