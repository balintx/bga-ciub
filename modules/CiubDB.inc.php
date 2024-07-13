<?php
/*

    BGA framework: © Gregory Isabelli <gisabelli@boardgamearena.com> & Emmanuel Colin <ecolin@boardgamearena.com>
    BGA-Ciub: a Board Game Arena implementation of the board game Ciúb
    Copyright (C) 2022  Balint Ruszki <balintx@balAAAAAAintx.me> (Remove the uppercase A-s)

    This program is free software: you can redistribute it and/or modify
    it under the terms of the GNU Affero General Public License as published
    by the Free Software Foundation, either version 3 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU Affero General Public License for more details.
    
    This code has been produced on the BGA studio platform for use on
    http://boardgamearena.com

    See http://en.boardgamearena.com/#!doc/Studio for more information.

    You should have received a copy of the GNU Affero General Public License
    along with this program.  If not, see <https://www.gnu.org/licenses/>.

*/

//namespace CiubGame;

//require('_ide.php');

abstract class LocationDB extends APP_DBObject
{
    /**
     * Retrieves the location value of the specified item from item_locations
     * 
     * @param string $item_type 
     * @param mixed $item_id 
     * @return string The location of the item 
     */
    public static function getItemLocation($item_type, $item_id)
    {
        return self::getUniqueValueFromDB(sprintf("SELECT location FROM item_locations WHERE item_type = '%s' AND item_id = '%s'", $item_type, $item_id));
    }

    /**
     * Moves an item within the item_locations table
     * 
     * The item will be removed from the database and recreated with the new location setting.
     * This is to update the internal id which is used to determine the order the items were placed
     * to the same location.
     * 
     * @see LocationDB::getFirstItemAt()
     * 
     * @param string $new_location
     * @param string $item_type
     * @param mixed $item_id
     */
    public static function setItemLocation($new_location, $item_type, $item_id)
    {
        self::DbQuery(sprintf("DELETE FROM item_locations WHERE item_type = '%s' AND item_id = '%s'", $item_type, $item_id));
        self::createItem($item_type, $item_id, $new_location);
    }

    /**
     * Creates an item within the item_locations table
     * 
     * @param string $item_type
     * @param mixed $item_id 
     * @param string $location (optional)
     * @return void 
     */
    public static function createItem($item_type, $item_id, $location = 'void')
    {
        self::DbQuery(sprintf("INSERT INTO item_locations (item_type, item_id, location) VALUES ('%s', '%s', '%s')", $item_type, $item_id, $location));
    }

    /**
     * Retrieves the most recent item at the specified location
     * 
     * @param mixed $location
     * @param mixed $item_type only select item of the specified type
     * @return $item_id|array{item_type: string, item_id: mixed}|NULL The first item at the specified location.
     * When param $item_type is specified, only the id is returned instead of an array.
     */
    public static function getFirstItemAt($location, $item_type = NULL)
    {
        return self::getUniqueValueFromDB(sprintf("SELECT item_id FROM item_locations WHERE location = '%s' AND item_type = '%s' ORDER BY id LIMIT 1", $location, $item_type));
    }

    /**
     * Retrieve an array of the item ids at the specified location
     * 
     * @param mixed $location
     * @param mixed $item_type only return items of the specified type
     * @return $item_id[] Array of items at the specified location.
     */
    public static function getItemsAt($location, $item_type = NULL)
    {
        return self::getObjectListFromDB(sprintf("SELECT item_id FROM item_locations WHERE location = '%s'  AND item_type = '%s'", $location, $item_type), true);
    }
}

abstract class CardDB extends APP_DBObject
{
    /**
     * @return Card[]
     * @deprecated
     */
    public static function getCardsByFileID(...$fileIDs)
    {
        $cards = [];
        foreach ($fileIDs as $id)
        {
            $staticCard = DeckGenerator::$FullDeck[$id];
            $card = new Card($id);
            $card->cardLetter = $staticCard['cardLetter'];
            $card->isOwl = $staticCard['isOwl'];
            $card->requiredDices = $staticCard['requiredDices'];
            $card->victoryPoints = $staticCard['victoryPoints'];
            $cards[] = $card;
        }
        return $cards;
    }

    /**
     * Inserts card ids into location database
     * @param array $card_ids
     */
    public static function createCards($card_ids)
    {
        //$valueLines = []; // deprecated, we do not need to store static info into SQL
        foreach ($card_ids as $card_id)
        {// 'INSERT INTO cards (fileID, cardLetter, isOwl, victoryPoints, requiredDices) VALUES (';
            //$valueLines[] = sprintf("('%s', '%s', '%b', '%s', '%s')", $card->fileID, $card->cardLetter, $card->isOwl, $card->victoryPoints, $card->requiredDices);
            LocationDB::createItem('card', $card_id, 'deck');
        }
        //self::DbQuery('INSERT INTO cards (fileID, cardLetter, isOwl, victoryPoints, requiredDices) VALUES ' . implode(', ', $valueLines) . ';'); */
    }
    
    public static function isAt($card_id, $location)
    {
        return LocationDB::getItemLocation('card', $card_id) == $location;
    }

    public static function moveTo($card_id, $location)
    {
        LocationDB::setItemLocation($location, 'card', $card_id);
    }
    
    /**
     *
     * @return $card_id[]
     */
    public static function getCardsAt($location)
    {
        return LocationDB::getItemsAt($location, 'card');
        //return self::getCardsByFileID($ids);
    }

    /**
     * Finds whether the card has a token on it (optionally by another player).
     * 
     * @param int $exclude_playerid (optional) if provided, this function returns true only if another player's token is on the card
     * @return bool
     */
    public static function hasToken($card_id, $exclude_playerid = NULL)
    {
        $token = LocationDB::getFirstItemAt('card_'.$card_id, 'token');
        /*

        token   |  playerid |  expect
        -----------------------------
        1             1        false   token != playerid is false
        1             2        true    \
        1            null      true    | => token != playerid is true  
        null          1        false   / - only mismatch is when token is null
        null         null      false   token != playerid is false

        */

        return $token != $exclude_playerid && $token;
    }
}

abstract class CubeDB extends APP_DBObject
{
    public static function allCubes($numPlayers) : array
    {
        $cubes = [];
        for ($i = 1; $i <= 7; $i++)
        {
            $cubes[] = 'cube_'.CubeColors::Blue.'_'.$i;
            $cubes[] = 'cube_'.CubeColors::Green.'_'.$i;
            $cubes[] = 'cube_'.CubeColors::Orange.'_'.$i;
            $cubes[] = 'cube_'.CubeColors::Pink.'_'.$i;
            $cubes[] = 'cube_'.CubeColors::Purple.'_'.$i;
            $cubes[] = 'cube_'.CubeColors::Yellow.'_'.$i;
        }
        for ($i = 1; $i <= $numPlayers * 5; $i++)
        {
            $cubes[] = 'cube_'.CubeColors::White.'_'.$i;
        }
        return $cubes;
    }

    public static function createAllCubes($numPlayers)
    {
        $ids = self::allCubes($numPlayers);
        $valueLines = [];
        foreach ($ids as $id)
        {
            $cube = CubeFactory::createFromId($id);
            $valueLines[] = sprintf("('%s', '%b', '%b', '%s')", $id, $cube->isActionActive(), $cube->isActive(), $cube->getFace());
            unset($cube);
            LocationDB::createItem('cube', $id, 'summarycard');
        }
        self::DbQuery('INSERT INTO cubes (id, is_action_active, is_active, current_face) VALUES ' . implode(', ', $valueLines).';');
    }

    /**
     * 
     * @param string $location
     * @return Cube[]
     */
    public static function getCubesAt($location, $include_inactive_cubes = false, $include_action_inactive_cubes = true)
    {
        $cubes = self::getCubes(LocationDB::getItemsAt($location, 'cube'));
        self::trace(var_export($cubes, true));
        return array_filter($cubes,
            function(Cube $cube) use ($include_inactive_cubes, $include_action_inactive_cubes) {
                return ($include_inactive_cubes || $cube->isActive()) && ($include_action_inactive_cubes || $cube->isActionActive());
            }
        );
    }

    /**
     * @param array $ids List of cube ids
     * @return Cube[]
     */
    public static function getCubes($ids)
    {
        $db = self::getObjectListFromDB('SELECT id, is_action_active, is_active, current_face FROM cubes WHERE id IN (\''.implode("','", $ids).'\') ');
        foreach ($db as $cubeData)
        {
            $cube = CubeFactory::createFromId($cubeData['id']);
            $cube->setFace($cubeData['current_face']);
            
            if ($cubeData['is_action_active'])
                $cube->setActionActive();
            if ($cubeData['is_active'])
                $cube->setActive();
            
            $cubes[$cubeData['id']] = $cube;
        }

        return $cubes ?? [];
    }

    public static function moveTo(Cube $cube, $location)
    {
        LocationDB::setItemLocation($location, 'cube', $cube->getId());
    }

    public static function updateDb(Cube $cube)
    {
        self::DbQuery(
            sprintf("UPDATE cubes SET is_action_active = '%b', is_active = '%b', current_face = '%s' WHERE id = '%s'",
                    $cube->isActionActive(), $cube->isActive(), $cube->getFace(), $cube->getId()
            )
        );
    }
}

abstract class PlayerDB extends APP_DBObject
{
    public static function mustSave($player_id, $set_flag = NULL)
    {
        if ($set_flag !== NULL)
        {
            self::DbQuery(sprintf("UPDATE player SET player_saveswap = '%b' WHERE player_id = '%s'", $set_flag, $player_id));
        }
        else
        {
            return (bool)self::getUniqueValueFromDB("SELECT player_saveswap FROM player WHERE player_id = '".$player_id."'");
        }
    }

    public static function wonCard($player_id, $set_flag = NULL)
    {
        if ($set_flag !== NULL)
        {
            self::DbQuery(sprintf("UPDATE player SET player_won_card = '%b' WHERE player_id = '%s'", $set_flag, $player_id));
        }
        else
        {
            return (bool)self::getUniqueValueFromDB("SELECT player_won_card FROM player WHERE player_id = '".$player_id."'");
        }
    }

    public static function saveCubes($player_id)
    {
        self::DbQuery(sprintf("UPDATE player SET player_restore = '%s' WHERE player_id = '%s'", implode(',', LocationDB::getItemsAt('dicetray_'.$player_id, 'cube')), $player_id));
    }

    public static function getSavedCubes($player_id)
    {
        return explode(',', self::getUniqueValueFromDB("SELECT player_restore FROM player WHERE player_id = '".$player_id."'"));
    }
}

/*
// Test: DeckGenerator::$FullDeck fileID is the same as the position in array for all cards
require('CiubDecks.inc.php');
require('CiubCards.inc.php');
require('CiubCubes.inc.php');
foreach (CardDB::getCardsByFileID(...array_keys(array_fill(0, count(DeckGenerator::$FullDeck) - 1, 1))) as $id => $card)
{
    assert($id == $card->fileID);
}
*/