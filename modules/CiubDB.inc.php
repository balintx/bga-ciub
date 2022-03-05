<?php

//namespace CiubGame;

//require('_ide.php');

abstract class LocationDB extends Table
{
    /**
     * @return mixed
     */
    public static function getItemLocation($item_type, $item_id)
    {
        return \Ciub::getUniqueValueFromDB(sprintf("SELECT location FROM item_locations WHERE item_type = '%s' AND item_id = '%s'", $item_type, $item_id));
    }

    public static function setItemLocation($new_location, $item_type, $item_id)
    {
        \Ciub::DbQuery(sprintf("UPDATE item_locations SET location = '%s' WHERE item_type = '%s' AND item_id = '%s'", $new_location, $item_type, $item_id));
    }

    public static function createItem($item_type, $item_id, $location = 'void')
    {
        \Ciub::DbQuery(sprintf("INSERT INTO item_locations (item_type, item_id, location) VALUES ('%s', '%s', '%s')", $item_type, $item_id, $location));
    }

    /**
     * Retrieve an array of the items at the specified location
     * 
     * @param mixed $location
     * @param mixed $item_type (optional) Only return the specified item types
     * @return array The items at the specified location
     */
    public static function getItemsAt($location, $item_type = NULL)
    {
        if ($item_type !== NULL)
            $item_type = " AND item_type = '".$item_type."'";

        return \Ciub::getObjectListFromDB(sprintf("SELECT item_type, item_id FROM item_locations WHERE location = '%s'".$item_type, $location));
    }
}

abstract class CardDB extends Table
{
    /**
     * @return Card[]
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
     * @param Card[] $cards
     */
    public static function createCards($cards)
    {
        //$valueLines = []; // deprecated, we do not need to store static info into SQL
        foreach ($cards as $card)
        {// 'INSERT INTO cards (fileID, cardLetter, isOwl, victoryPoints, requiredDices) VALUES (';
            //$valueLines[] = sprintf("('%s', '%s', '%b', '%s', '%s')", $card->fileID, $card->cardLetter, $card->isOwl, $card->victoryPoints, $card->requiredDices);
            LocationDB::createItem('card', $card->fileID, 'deck');
        }
        //\Ciub::DbQuery('INSERT INTO cards (fileID, cardLetter, isOwl, victoryPoints, requiredDices) VALUES ' . implode(', ', $valueLines) . ';'); */
    }

    public static function isAt(Card $card, $location)
    {
        return LocationDB::getItemLocation('card', $card->fileID) == $location;
    }

    public static function moveTo(Card $card, $location)
    {
        LocationDB::setItemLocation($location, 'card', $card->fileID);
    }

    public static function getCardsAt($location)
    {
        $dbResult = LocationDB::getItemsAt($location, 'card');
        $ids = [];

        foreach ($dbResult as $resultArray)
        {
            $ids[] = $resultArray['item_id'];
        }

        return self::getCardsByFileID($ids);
    }

    /**
     * Finds whether the card has a token on it.
     * 
     * @param Card $card
     * @param int $by_playerid (optional) if provided, this function returns true only if this player's token is on the card
     * @return bool
     */
    public static function hasToken(Card $card, $by_playerid = NULL)
    {
        return
            count(
                array_filter(
                    LocationDB::getItemsAt('card_' . $card->fileID, 'token'),
                    function($item) use ($by_playerid) { 
                        return $by_playerid === NULL || $item['item_id'] == $by_playerid;
                    }
                )
            ) > 0;
    }
}

abstract class CubeDB extends Table
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
        \Ciub::DbQuery('INSERT INTO cubes (id, is_action_active, is_active, current_face) VALUES ' . implode(', ', $valueLines).';');
    }

    public static function moveTo(Cube $cube, $location)
    {
        LocationDB::setItemLocation($location, 'cube', $cube->getId());
    }

    public static function updateDb(Cube $cube)
    {
        \Ciub::DbQuery(
            sprintf("UPDATE cubes SET is_action_active = '%b', is_active = '%b', current_face = '%s' WHERE id = '%s'",
                    $cube->isActionActive(), $cube->isActive(), $cube->getFace(), $cube->getId()
            )
        );
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