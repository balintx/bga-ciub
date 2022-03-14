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

//require('misc/_ide.php');

require_once( APP_GAMEMODULE_PATH.'module/table/table.game.php' );
require_once('modules/CiubDecks.inc.php');
require_once('modules/CiubCubes.inc.php');
require_once('modules/CiubCards.inc.php');
require_once('modules/CiubDB.inc.php');
/*
use CiubGame\DeckGenerator;
use CiubGame\Cube;
use CiubGame\CardDB;
use CiubGame\LocationDB;
use CiubGame\CubeDB;
use CiubGame\CubeFactory;*/

class Ciub extends Table
{
	function __construct( )
	{
        // Your global variables labels:
        //  Here, you can assign labels to global variables you are using for this game.
        //  You can use any number of global variables with IDs between 10 and 99.
        //  If your game has options (variants), you also have to associate here a label to
        //  the corresponding ID in gameoptions.inc.php.
        // Note: afterwards, you can get/set the global variables with getGameStateValue/setGameStateInitialValue/setGameStateValue
        parent::__construct();

        
        self::initGameStateLabels( array( 
			'game_length' => 100
            //    "my_first_global_variable" => 10,
            //    "my_second_global_variable" => 11,
            //      ...
            //    "my_first_game_variant" => 100,
            //    "my_second_game_variant" => 101,
            //      ...
        ) );        
	}
	
    protected function getGameName( )
    {
		// Used for translations and stuff. Please do not modify.
        return "ciub";
    }	

    /*
        setupNewGame:
        
        This method is called only once, when a new game is launched.
        In this method, you must setup the game according to the game rules, so that
        the game is ready to be played.
    */
    protected function setupNewGame( $players, $options = array() )
    {    
        // Set the colors of the players with HTML color code
        // The default below is red/green/blue/orange/brown
        // The number of colors defined here must correspond to the maximum number of players allowed for the game
        $gameinfos = self::getGameinfos();
        $default_colors = $gameinfos['player_colors'];
 
        // Create players
        // Note: if you added some extra field on "player" table in the database (dbmodel.sql), you can initialize it there.
        $sql = "INSERT INTO player (player_id, player_color, player_canal, player_name, player_avatar) VALUES ";
        $values = array();
        foreach( $players as $player_id => $player )
        {
            $color = array_shift( $default_colors );
            $values[] = "('".$player_id."','$color','".$player['player_canal']."','".addslashes( $player['player_name'] )."','".addslashes( $player['player_avatar'] )."')";
        }
        $sql .= implode( ',', $values );
        self::DbQuery( $sql );
        self::reattributeColorsBasedOnPreferences( $players, $gameinfos['player_colors'] );
        self::reloadPlayersBasicInfos();
        
        /************ Start the game initialization *****/

        // Init global values with their initial values
        //self::setGameStateInitialValue( 'my_first_global_variable', 0 );
        
        // Init game statistics
        // (note: statistics used in this file must be defined in your stats.inc.php file)
        //self::initStat( 'table', 'table_teststat1', 0 );    // Init a table statistics
        //self::initStat( 'player', 'player_teststat1', 0 );  // Init a player statistics (for all players)

        // TODO: setup the initial game situation here
		$numPlayers = count($players);
		$isShortGame = self::getGameStateValue('game_length') == 1;

		// Create deck
		$starterDeck = DeckGenerator::Generate($numPlayers, $isShortGame, NULL, 'bga_rand');
		array_walk($starterDeck, function(&$card) { $card = $card['fileID']; });
		//$starterDeck = CardDB::getCardsByFileID(...$starterDeck);
		CardDB::createCards(array_reverse($starterDeck));

		// Fill bottom and top row with cards
		for ($i = 0; $i <= $numPlayers; $i++)
		{
			CardDB::moveTo(array_pop($starterDeck), 'row_bottom');
		}

		for ($i = 0; $i <= $numPlayers; $i++)
		{
			CardDB::moveTo(array_pop($starterDeck), 'row_top');
		}

		// Create cubes
		CubeDB::createAllCubes($numPlayers);

		// Give white cubes to players
		foreach (array_keys($players) as $i => $player_id)
		{
			for ($j = 1; $j <= 5; $j++)
			{
				LocationDB::setItemLocation('player_'.$player_id, 'cube', 'cube_'.CubeColors::White.'_'.$i*5+$j);
			}
		}

		// Create tokens
		foreach (array_keys($players) as $player_id)
		{
			LocationDB::createItem('token', $player_id, 'player_'.$player_id);
		}

        // Activate first player (which is in general a good idea :) )
        $this->activeNextPlayer();

        /************ End of the game initialization *****/
    }

    /*
        getAllDatas: 
        
        Gather all informations about current game situation (visible by the current player).
        
        The method is called each time the game interface is displayed to a player, ie:
        _ when the game starts
        _ when a player refreshes the game page (F5)
    */
    protected function getAllDatas()
    {
        $result = array();
    
        $current_player_id = self::getCurrentPlayerId();    // !! We must only return informations visible by this player !!
    
        // Get information about players
        // Note: you can retrieve some extra field you added for "player" table in "dbmodel.sql" if you need it.
        $sql = "SELECT player_id id, player_score score FROM player ";
        $result['players'] = self::getCollectionFromDb( $sql );
  
        // TODO: Gather all information about current game situation (visible by player $current_player_id).
  
        return $result;
    }

    /*
        getGameProgression:
        
        Compute and return the current game progression.
        The number returned must be an integer beween 0 (=the game just started) and
        100 (= the game is finished or almost finished).
    
        This method is called each time we are in a game state with the "updateGameProgression" property set to true 
        (see states.inc.php)
    */
    function getGameProgression()
    {
        // TODO: compute and return the game progression

        return 0;
    }


//////////////////////////////////////////////////////////////////////////////
//////////// Utility functions
////////////    

    /*
        In this space, you can put any utility methods useful for your game logic
    */

	function refillTopRow()
	{
		$card_id = LocationDB::getFirstItemAt('deck', 'card');
		if (!$card_id) // Opus Magnum should not be moved to the top row
			return;

		self::moveCard($card_id, 'row_top');
	}
	/**
	 * 
	 * @throws BGAUserException throws an error when the target cube is in an invalid location
	 * @param mixed $cube_id 
	 * @param array $valid_locations 
	 */
	function checkCubeLocation($cube_id, $valid_locations)
	{
		if (!in_array(LocationDB::getItemLocation('cube', $cube_id), $valid_locations, true))
			throw new BgaUserException(self::_('You can not target that cube'));
	}

	function moveCard($card_id, $location)
	{
		//$previousLocation = LocationDB::getItemLocation('card', $location);
		LocationDB::setItemLocation($location, 'card', $card_id);
		self::notifyAllPlayers('cardMoved', '', ['card_id' => $card_id, 'previous_location' => 'deck', 'new_location' => 'row_top']);
	}

	function moveCube($cube_id, $location)
	{
		//$previousLocation = LocationDB::getItemLocation('cube', $location);
		LocationDB::setItemLocation($location, 'cube', $cube_id);
		self::notifyAllPlayers('cubeMoved', '', ['cube_id' => $cube_id, /*'previous_location' => $previousLocation,*/ 'new_location' => 'diceaction_initiator']);
	}

//////////////////////////////////////////////////////////////////////////////
//////////// Player actions
//////////// 

    /*
        Each time a player is doing some game action, one of the methods below is called.
        (note: each method below must match an input method in ciub.action.php)
    */

    /*
    
    Example:

    function playCard( $card_id )
    {
        // Check that this is the player's turn and that it is a "possible action" at this game state (see states.inc.php)
        self::checkAction( 'playCard' ); 
        
        $player_id = self::getActivePlayerId();
        
        // Add your game logic to play a card there 
        ...
        
        // Notify all players about the card played
        self::notifyAllPlayers( "cardPlayed", clienttranslate( '${player_name} plays ${card_name}' ), array(
            'player_id' => $player_id,
            'player_name' => self::getActivePlayerName(),
            'card_name' => $card_name,
            'card_id' => $card_id
        ) );
          
    }
    
    */
	
	function jmp_p1PromptPlaceToken()
	{
		self::checkAction('jmp_p1PromptPlaceToken');
		switch ($this->gamestate->state_id())
		{
			case 10: // p1PromptDecideAction
				// ${you} can place your token or remove one card from the top row

				$this->gamestate->nextState('p1PromptPlaceToken');
			break;

			default:
				$stateInfo = $this->gamestate->state();
				$id = $this->gamestate->state_id();
				throw new BgaVisibleSystemException("Action 'jmp_p1PromptPlaceToken' has been called from an invalid state ($id: ".$stateInfo['name'].")");
			break;
		}
	}

	function jmp_p1PromptRemoveTopCard()
	{
		self::checkAction('jmp_p1PromptRemoveTopCard');
		switch ($this->gamestate->state_id())
		{
			case 10: // p1PromptDecideAction
				// ${you} can place your token or remove one card from the top row
				
				$this->gamestate->nextState('p1PromptRemoveTopCard');
			break;

			default:
				$stateInfo = $this->gamestate->state();
				$id = $this->gamestate->state_id();
				throw new BgaVisibleSystemException("Action 'jmp_p1PromptRemoveTopCard' has been called from an invalid state ($id: ".$stateInfo['name'].")");
			break;
		}
	}

	function jmp_skipP1()
	{
		self::checkAction('jmp_skipP1');
		switch ($this->gamestate->state_id())
		{
			case 10: // p1PromptDecideAction
				// ${you} can place your token or remove one card from the top row
				
				$this->gamestate->nextState('p2Roll');
			break;

			default:
				$stateInfo = $this->gamestate->state();
				$id = $this->gamestate->state_id();
				throw new BgaVisibleSystemException("Action 'jmp_skipP1' has been called from an invalid state ($id: ".$stateInfo['name'].")");
			break;
		}
	}

	function p1Undo()
	{
		self::checkAction('p1Undo');
		switch ($this->gamestate->state_id())
		{
			case 11: // p1PromptPlaceToken
				// ${you} place your token on a spell card
				
				$this->gamestate->nextState('p1PromptDecideAction');
			break;

			case 12: // p1PromptRemoveTopCard
				// ${you} remove a spell card from the top row
				
				$this->gamestate->nextState('p1PromptDecideAction');
			break;

			default:
				$stateInfo = $this->gamestate->state();
				$id = $this->gamestate->state_id();
				throw new BgaVisibleSystemException("Action 'p1Undo' has been called from an invalid state ($id: ".$stateInfo['name'].")");
			break;
		}
	}

	function p1DoPlaceToken($card_id)
	{
		self::checkAction('p1DoPlaceToken');
		switch ($this->gamestate->state_id())
		{
			case 11: // p1PromptPlaceToken
				// ${you} place your token on a spell card
				$playerId = self::getCurrentPlayerId();
				
				if (LocationDB::getItemLocation('card', $card_id) != 'row_top')
				{
					throw new BgaUserException(self::_("Target card is not in the top row"));
				}
				
				if (CardDB::hasToken($card_id))
				{
					throw new BgaUserException(self::_("That card has a token"));
				}

				LocationDB::setItemLocation('card_' . $card_id, 'token', $playerId);
				self::notifyAllPlayers('tokenPlaced', clienttranslate('${player_name} has placed his/her token on a card'), ['player_id' => $playerId,
				'player_name' => self::getActivePlayerName(), 'card_id' => $card_id]);
				
				$this->gamestate->nextState('p2Roll');
				//$this->gamestate->nextState('p1PromptDecideAction');		
			break;

			default:
				$stateInfo = $this->gamestate->state();
				$id = $this->gamestate->state_id();
				throw new BgaVisibleSystemException("Action 'p1DoPlaceToken' has been called from an invalid state ($id: ".$stateInfo['name'].")");
			break;
		}
	}

	function p1DoRemoveTopCard($card_id)
	{
		self::checkAction('p1DoRemoveTopCard');
		switch ($this->gamestate->state_id())
		{
			case 12: // p1PromptRemoveTopCard
				// ${you} remove a spell card from the top row
				$playerId = self::getCurrentPlayerId();

				if (LocationDB::getItemLocation('card', $card_id) != 'row_top')
				{
					throw new BgaUserException(self::_("Target card is not in the top row"));
				}

				if (CardDB::hasToken($card_id))
				{
					throw new BgaUserException(self::_("That card has a token"));
				}

				CardDB::moveTo($card_id, 'void');
				self::notifyAllPlayers('topCardRemoved', clienttranslate('${player_name} has removed a card from the top row'), ['player_id' => $playerId, 'player_name' => self::getActivePlayerName(), 'card_id' => $card_id]);
				
				self::refillTopRow();

				$this->gamestate->nextState('p2Roll');
				//$this->gamestate->nextState('p1PromptDecideAction');
			break;

			default:
				$stateInfo = $this->gamestate->state();
				$id = $this->gamestate->state_id();
				throw new BgaVisibleSystemException("Action 'p1DoRemoveTopCard' has been called from an invalid state ($id: ".$stateInfo['name'].")");
			break;
		}
	}

	function jmp_skipP2DiceActionPhase()
	{
		self::checkAction('jmp_skipP2DiceActionPhase');
		switch ($this->gamestate->state_id())
		{
			case 22: // p2PromptChooseDiceActions
				// ${you} may modify your dice using dice actions
				
				$this->gamestate->nextState('chkHasP2ActiveSkulls');
			break;

			default:
				$stateInfo = $this->gamestate->state();
				$id = $this->gamestate->state_id();
				throw new BgaVisibleSystemException("Action 'jmp_skipP2DiceActionPhase' has been called from an invalid state ($id: ".$stateInfo['name'].")");
			break;
		}
	}

	function jmp_p2DiceAction($cube_id)
	{
		self::checkAction('jmp_p2DiceAction');
		switch ($this->gamestate->state_id())
		{
			case 22: // p2PromptChooseDiceActions
				// ${you} may modify your dice using dice actions
				$activePlayer = $this->getActivePlayerId();
				$cube = CubeFactory::createFromId($cube_id);
				self::checkCubeLocation($cube_id, ['player_'.$activePlayer]);
				if (!$cube->isActionActive())
					throw new BgaUserException(self::_("That dice has no active action"));
				if (!in_array($cube->getFace(), CubeFaces::All_DiceActions))
					throw new BgaUserException(self::_("That dice has no active action"));
				self::moveCube($cube_id, 'diceaction_initiator');
				$this->gamestate->nextState('p2DiceAction');

			break;

			default:
				$stateInfo = $this->gamestate->state();
				$id = $this->gamestate->state_id();
				throw new BgaVisibleSystemException("Action 'jmp_p2DiceAction' has been called from an invalid state ($id: ".$stateInfo['name'].")");
			break;
		}
	}

	function p2UndoDiceAction() // todo replace dice(s) to the previous position
	{
		self::checkAction('p2UndoDiceAction');
		switch ($this->gamestate->state_id())
		{
			case 51: // p2PromptDiceActionReRollDie
				// ${you} must choose a dice to re-roll
				
				$this->gamestate->nextState('p2PromptChooseDiceActions');
			break;

			case 53: // p2PromptDiceActionSwapDie
				// ${you} must choose a dice to replace ${color} dice
				
				$this->gamestate->nextState('p2PromptChooseDiceActions');
			break;

			case 55: // p2PromptDiceActionAdjustFace
				// ${you} must choose a dice to adjust
				
				$this->gamestate->nextState('p2PromptChooseDiceActions');
			break;

			case 56: // p2PromptDiceActionAdjustFace2
				// ${you} must adjust the ${color} dice
				
				$this->gamestate->nextState('p2PromptChooseDiceActions');
			break;

			default:
				$stateInfo = $this->gamestate->state();
				$id = $this->gamestate->state_id();
				throw new BgaVisibleSystemException("Action 'p2UndoDiceAction' has been called from an invalid state ($id: ".$stateInfo['name'].")");
			break;
		}
	}

	function p2DoDiceActionReRollDie()
	{
		self::checkAction('p2DoDiceActionReRollDie');
		switch ($this->gamestate->state_id())
		{
			case 51: // p2PromptDiceActionReRollDie
				// ${you} must choose a dice to re-roll
				
				$this->gamestate->nextState('chkHasP2DiceActions');
			break;

			default:
				$stateInfo = $this->gamestate->state();
				$id = $this->gamestate->state_id();
				throw new BgaVisibleSystemException("Action 'p2DoDiceActionReRollDie' has been called from an invalid state ($id: ".$stateInfo['name'].")");
			break;
		}
	}

	function p2DoDiceActionSwapDie()
	{
		self::checkAction('p2DoDiceActionSwapDie');
		switch ($this->gamestate->state_id())
		{
			case 53: // p2PromptDiceActionSwapDie
				// ${you} must choose a dice to replace ${color} dice
				
				$this->gamestate->nextState('chkHasP2DiceActions');
			break;

			default:
				$stateInfo = $this->gamestate->state();
				$id = $this->gamestate->state_id();
				throw new BgaVisibleSystemException("Action 'p2DoDiceActionSwapDie' has been called from an invalid state ($id: ".$stateInfo['name'].")");
			break;
		}
	}

	function jmp_p2PromptDiceActionAdjustFace2()
	{
		self::checkAction('jmp_p2PromptDiceActionAdjustFace2');
		switch ($this->gamestate->state_id())
		{
			case 55: // p2PromptDiceActionAdjustFace
				// ${you} must choose a dice to adjust
				
				$this->gamestate->nextState('p2PromptDiceActionAdjustFace2');
			break;

			default:
				$stateInfo = $this->gamestate->state();
				$id = $this->gamestate->state_id();
				throw new BgaVisibleSystemException("Action 'jmp_p2PromptDiceActionAdjustFace2' has been called from an invalid state ($id: ".$stateInfo['name'].")");
			break;
		}
	}

	function p2DoDiceActionAdjustFace()
	{
		self::checkAction('p2DoDiceActionAdjustFace');
		switch ($this->gamestate->state_id())
		{
			case 56: // p2PromptDiceActionAdjustFace2
				// ${you} must adjust the ${color} dice
				
				$this->gamestate->nextState('chkHasP2DiceActions');
			break;

			default:
				$stateInfo = $this->gamestate->state();
				$id = $this->gamestate->state_id();
				throw new BgaVisibleSystemException("Action 'p2DoDiceActionAdjustFace' has been called from an invalid state ($id: ".$stateInfo['name'].")");
			break;
		}
	}

	function p2DoPutDicesInTray()
	{
		self::checkAction('p2DoPutDicesInTray');
		switch ($this->gamestate->state_id())
		{
			case 26: // p2PromptMustSaveDices
				// ${you} must place at least one dice into your dice tray
				
				$this->gamestate->nextState('chkAreP2AllDicesInDiceTray');
			break;

			case 27: // p2PromptCanSaveDices
				// ${you} may place dices into your dice tray
				
				$this->gamestate->nextState('chkAreP2AllDicesInDiceTray');
			break;

			default:
				$stateInfo = $this->gamestate->state();
				$id = $this->gamestate->state_id();
				throw new BgaVisibleSystemException("Action 'p2DoPutDicesInTray' has been called from an invalid state ($id: ".$stateInfo['name'].")");
			break;
		}
	}

	function jmp_p2Roll()
	{
		self::checkAction('jmp_p2Roll');
		switch ($this->gamestate->state_id())
		{
			case 27: // p2PromptCanSaveDices
				// ${you} may place dices into your dice tray
				
				$this->gamestate->nextState('p2Roll');
			break;

			default:
				$stateInfo = $this->gamestate->state();
				$id = $this->gamestate->state_id();
				throw new BgaVisibleSystemException("Action 'jmp_p2Roll' has been called from an invalid state ($id: ".$stateInfo['name'].")");
			break;
		}
	}

	function jmp_skipP3SpellWin()
	{
		self::checkAction('jmp_skipP3SpellWin');
		switch ($this->gamestate->state_id())
		{
			case 60: // p3PromptSelectWonSpell
				// ${you} can cast a spell and gain that spell card
				
				$this->gamestate->nextState('p3PromptBonusDice');
			break;

			default:
				$stateInfo = $this->gamestate->state();
				$id = $this->gamestate->state_id();
				throw new BgaVisibleSystemException("Action 'jmp_skipP3SpellWin' has been called from an invalid state ($id: ".$stateInfo['name'].")");
			break;
		}
	}

	function p3DoWinSpellCard()
	{
		self::checkAction('p3DoWinSpellCard');
		switch ($this->gamestate->state_id())
		{
			case 60: // p3PromptSelectWonSpell
				// ${you} can cast a spell and gain that spell card
				
				$this->gamestate->nextState('chkCountP3Deck');
			break;

			default:
				$stateInfo = $this->gamestate->state();
				$id = $this->gamestate->state_id();
				throw new BgaVisibleSystemException("Action 'p3DoWinSpellCard' has been called from an invalid state ($id: ".$stateInfo['name'].")");
			break;
		}
	}

	function p3DoRefillBottomAndTopRow()
	{
		self::checkAction('p3DoRefillBottomAndTopRow');
		switch ($this->gamestate->state_id())
		{
			case 81: // p3PromptRefillBottomRow
				// ${you} must move a spell card from the top row to the bottom row
				
				$this->gamestate->nextState('chkHasP3MoreThanFive');
			break;

			default:
				$stateInfo = $this->gamestate->state();
				$id = $this->gamestate->state_id();
				throw new BgaVisibleSystemException("Action 'p3DoRefillBottomAndTopRow' has been called from an invalid state ($id: ".$stateInfo['name'].")");
			break;
		}
	}

	function p3DoGainBonusDice()
	{
		self::checkAction('p3DoGainBonusDice');
		switch ($this->gamestate->state_id())
		{
			case 61: // p3PromptBonusDice
				// ${you} gain an extra dice for not casting any spells
				
				$this->gamestate->nextState('chkCanP3Trade');
			break;

			default:
				$stateInfo = $this->gamestate->state();
				$id = $this->gamestate->state_id();
				throw new BgaVisibleSystemException("Action 'p3DoGainBonusDice' has been called from an invalid state ($id: ".$stateInfo['name'].")");
			break;
		}
	}

	function p3DoReduceToFive()
	{
		self::checkAction('p3DoReduceToFive');
		switch ($this->gamestate->state_id())
		{
			case 63: // p3PromptReduceToFive
				// ${you} must give back ${count} dice(s)
				
				$this->gamestate->nextState('chkCanP3Trade');
			break;

			default:
				$stateInfo = $this->gamestate->state();
				$id = $this->gamestate->state_id();
				throw new BgaVisibleSystemException("Action 'p3DoReduceToFive' has been called from an invalid state ($id: ".$stateInfo['name'].")");
			break;
		}
	}

	function jmp_skipP3Trade()
	{
		self::checkAction('jmp_skipP3Trade');
		switch ($this->gamestate->state_id())
		{
			case 65: // p3PromptWantTrade
				// ${you} can trade one dice for two other dices
				
				$this->gamestate->nextState('chkCanP3TradeForWhite');
			break;

			default:
				$stateInfo = $this->gamestate->state();
				$id = $this->gamestate->state_id();
				throw new BgaVisibleSystemException("Action 'jmp_skipP3Trade' has been called from an invalid state ($id: ".$stateInfo['name'].")");
			break;
		}
	}

	function p3Undo()
	{
		self::checkAction('p3Undo');
		switch ($this->gamestate->state_id())
		{
			case 65: // p3PromptWantTrade
				// ${you} can trade one dice for two other dices
				
				$this->gamestate->nextState('p3RestoreDiceTray');
			break;

			case 67: // p3PromptTradeForWhite
				// ${you} can trade any dice for white dice
				
				$this->gamestate->nextState('p3RestoreDiceTray');
			break;

			case 69: // p3PromptDiscardDices
				// ${you} can have a maximum of ${count} ${color} dices
				
				$this->gamestate->nextState('p3RestoreDiceTray');
			break;

			default:
				$stateInfo = $this->gamestate->state();
				$id = $this->gamestate->state_id();
				throw new BgaVisibleSystemException("Action 'p3Undo' has been called from an invalid state ($id: ".$stateInfo['name'].")");
			break;
		}
	}

	function p3DoTradeDices()
	{
		self::checkAction('p3DoTradeDices');
		switch ($this->gamestate->state_id())
		{
			case 65: // p3PromptWantTrade
				// ${you} can trade one dice for two other dices
				
				$this->gamestate->nextState('chkCanP3Trade');
			break;

			default:
				$stateInfo = $this->gamestate->state();
				$id = $this->gamestate->state_id();
				throw new BgaVisibleSystemException("Action 'p3DoTradeDices' has been called from an invalid state ($id: ".$stateInfo['name'].")");
			break;
		}
	}

	function p3DoTradeForWhite()
	{
		self::checkAction('p3DoTradeForWhite');
		switch ($this->gamestate->state_id())
		{
			case 67: // p3PromptTradeForWhite
				// ${you} can trade any dice for white dice
				
				$this->gamestate->nextState('chkLimitsP3');
			break;

			default:
				$stateInfo = $this->gamestate->state();
				$id = $this->gamestate->state_id();
				throw new BgaVisibleSystemException("Action 'p3DoTradeForWhite' has been called from an invalid state ($id: ".$stateInfo['name'].")");
			break;
		}
	}

	function jmp_chkCanP3TradeForWhite()
	{
		self::checkAction('jmp_chkCanP3TradeForWhite');
		switch ($this->gamestate->state_id())
		{
			case 69: // p3PromptDiscardDices
				// ${you} can have a maximum of ${count} ${color} dices
				
				$this->gamestate->nextState('chkCanP3TradeForWhite');
			break;

			default:
				$stateInfo = $this->gamestate->state();
				$id = $this->gamestate->state_id();
				throw new BgaVisibleSystemException("Action 'jmp_chkCanP3TradeForWhite' has been called from an invalid state ($id: ".$stateInfo['name'].")");
			break;
		}
	}

	function p3DoDiscardDices()
	{
		self::checkAction('p3DoDiscardDices');
		switch ($this->gamestate->state_id())
		{
			case 69: // p3PromptDiscardDices
				// ${you} can have a maximum of ${count} ${color} dices
				
				$this->gamestate->nextState('chkLimitsP3');
			break;

			default:
				$stateInfo = $this->gamestate->state();
				$id = $this->gamestate->state_id();
				throw new BgaVisibleSystemException("Action 'p3DoDiscardDices' has been called from an invalid state ($id: ".$stateInfo['name'].")");
			break;
		}
	}

    
//////////////////////////////////////////////////////////////////////////////
//////////// Game state arguments
////////////

    /*
        Here, you can create methods defined as "game state arguments" (see "args" property in states.inc.php).
        These methods function is to return some additional information that is specific to the current
        game state.
    */

    /*
    
    Example for game state "MyGameState":
    
    function argMyGameState()
    {
        // Get some values from the current game situation in database...
    
        // return values:
        return array(
            'variable1' => $value1,
            'variable2' => $value2,
            ...
        );
    }    
    */

//////////////////////////////////////////////////////////////////////////////
//////////// Game state actions
////////////

    /*
        Here, you can create methods defined as "game state actions" (see "action" property in states.inc.php).
        The action method of state X is called everytime the current game state is set to X.
    */
    
    /*
    
    Example for game state "MyGameState":

    function stMyGameState()
    {
        // Do some stuff ...
        
        // (very often) go to another gamestate
        $this->gamestate->nextState( 'some_gamestate_transition' );
    }    
    */

	function stchkIsP1WonByActivePlayer()
	{
		// Checking if ${actplayer} has already won the Opus Magnum
		$activePlayer = $this->getActivePlayerId();
		if (LocationDB::getItemLocation('card', 0) == 'player_' . $activePlayer)
		{
			$this->gamestate->nextState('stGameEnd');
		}
		else
		{
			$this->gamestate->nextState('chkIsP1OpusAvailable');
		}
	}
	
	function stchkIsP1OpusAvailable()
	{
		// Checking if ${actplayer} can place a token or remove a card from the top row
		if (LocationDB::getItemLocation('card', 0) == 'deck')
		{
			$this->gamestate->nextState('p1PromptDecideAction');
		}
		else
		{
			$this->gamestate->nextState('p2Roll');
		}
	}
	
	function stp2Roll()
	{
		// ${actplayer} is rolling dies that are not in his/her dice tray
		$activePlayer = $this->getActivePlayerId();
		$cubes = CubeDB::getCubesAt('player_'.$activePlayer, true, true);

		$logdata = [];
		foreach ($cubes as $cube)
		{
			$logdata[$cube->getId()] = ['old' => $cube->getFace()];
			$cube->doRoll("bga_rand");
			$logdata[$cube->getId()]['new'] = $cube->getFace();
			CubeDB::updateDb($cube);
		}

		self::notifyAllPlayers('diceRerolled', '${player_name} rerolls dices not in his/her dice tray',
			['player_name' => $this->getActivePlayerName(), 'dices' => $logdata]);
		$this->gamestate->nextState('chkHasP2DiceActions');
	}

	function stchkHasP2DiceActions()
	{
		// Checking if there are any active dies with actions
		$activePlayer = $this->getActivePlayerId();
		if (self::getUniqueValueFromDB(
			"SELECT COUNT(*) FROM cubes
				INNER JOIN item_locations
				ON item_locations.item_id=cubes.id
				WHERE item_locations.item_type = 'cube'
					AND item_locations.location = 'player_".$activePlayer."'
					AND cubes.is_action_active = 1
					AND cubes.is_active = 1
					AND cubes.current_face IN (".implode(',', CubeFaces::All_DiceActions).") "
			) > 0)
			$this->gamestate->nextState('p2PromptChooseDiceActions');
		else
			$this->gamestate->nextState('chkHasP2ActiveSkulls');
	}
	
	function stp2DiceAction()
	{
		// Preparing for the correspondent dice action
		$cube = CubeDB::getCubesAt('diceaction_initiator');
		switch ($cube[0]->getFace())
		{
			case CubeFaces::Action_Reroll:
				$this->gamestate->nextState('p2PromptDiceActionReRollDie');
			break;
			case CubeFaces::Action_Swap:
				$this->gamestate->nextState('p2PromptDiceActionSwapDie');
			break;
			case CubeFaces::Action_Adjust:
				$this->gamestate->nextState('p2PromptDiceActionAdjustFace');
			break;
			default:
				throw new BgaVisibleSystemException('Dice has unknown action ('.print_r($cube, true).')');
			break;
		}
	}
	
	function stchkHasP2ActiveSkulls()
	{
		// [arg]${actplayer} must place dices with skulls into his/her dice tray
		$activePlayer = $this->getActivePlayerId();
		$cubes = CubeDB::getCubesAt('player_'.$activePlayer);
		$cubes = array_filter($cubes, function(Cube $cube) { return $cube->getFace() == CubeFaces::Action_Skull; });
		if (count($cubes) > 0)
		{
			self::notifyAllPlayers('skullsMoved', '${player_name} places ${count} skull(s) into his/her dice tray', ['player_name' => $this->getActivePlayerName(), 'count' => count($cubes)]);
			foreach ($cubes as $cube)
			{
				self::moveCube($cube->getId(), "dicetray_".$activePlayer);
			}
		}
		$this->gamestate->nextState('chkAreP2AllDicesInDiceTray');
	}
	
	function stchkAreP2AllDicesInDiceTray()
	{
		// Checking if all dices are saved or not
		$activePlayer = $this->getActivePlayerId();
		if (count(CubeDB::getCubesAt('player_'.$activePlayer, true, true)) > 0)
			$this->gamestate->nextState('chkMustP2SaveOneDice');
		else
			$this->gamestate->nextState('chkCanP3CastSpell');
	}
	
	function stchkMustP2SaveOneDice()
	{
		// ${actplayer} must save at least one dice if not saved yet or the Swap Die action was not used
		$this->gamestate->nextState('p2PromptMustSaveDices');
		$this->gamestate->nextState('p2PromptCanSaveDices');
	}
	
	function stchkCanP3CastSpell()
	{
		// Checking if any spell cards can be won
		$this->gamestate->nextState('p3PromptSelectWonSpell');
		$this->gamestate->nextState('p3PromptBonusDice');
	}
	
	function stchkCountP3Deck()
	{
		// Checking the remaining amount of cards in the deck
		switch (count(CardDB::getCardsAt('deck')))
		{
			case 1:
				$this->gamestate->nextState('p3WinOpus');
			break;
			case 0:
				$this->gamestate->nextState('chkHasP3MoreThanFive');
			break;
			default:
				$this->gamestate->nextState('p3PromptRefillBottomRow');
			break;
		}
	}
	
	function stp3WinOpus()
	{
		// "${actplayer} wins the Opus Magnum
		$opus = LocationDB::getFirstItemAt('deck', 'card');
		if ($opus != 0)
			throw new BgaVisibleSystemException("Opus was not the only item in the deck, WinOpus failed (card drawn: $opus)");

		$activePlayer = $this->getActivePlayerId();
		self::moveCard(0, 'player_'.$activePlayer);
		self::notifyAllPlayers('opusWon', '${player_name} has won the Opus Magnum', ['player_id' => $activePlayer, 'player_name' => $this->getActivePlayerName()]);
		$this->gamestate->nextState('chkCountP3Deck');
	}
	
	function stchkHasP3MoreThanFive()
	{
		// ${actplayer} can only have 5 dices
		$activePlayer = $this->getActivePlayerId();
		if (count(CubeDB::getCubesAt('dicetray_'.$activePlayer, true)) > 5)
			$this->gamestate->nextState('p3PromptReduceToFive');
		else
			$this->gamestate->nextState('chkCanP3Trade');
	}
	
	function stchkCanP3Trade()
	{
		// Checking if there is at least one saved dice with "Trade 2 for 1" face
		$activePlayer = $this->getActivePlayerId();
		if (count(
				array_filter(
					CubeDB::getCubesAt('dicetray_'.$activePlayer, true),
					function(Cube $cube) {
						return $cube->getFace() == CubeFaces::Action_Trade;
					}
				)
			) > 0)
			$this->gamestate->nextState('p3PromptWantTrade');
		else
			$this->gamestate->nextState('chkCanP3TradeForWhite');
		
	}
	
	function stp3RestoreDiceTray()
	{
		// ${actplayer} restores their dice tray and starts over
		$this->undoRestorePoint();
		$this->gamestate->nextState('chkCanP3CastSpell');
		$this->gamestate->nextState('chkHasP3MoreThanFive');
	}
	
	function stchkCanP3TradeForWhite()
	{
		// Checking if player has any non-white dices
		$this->gamestate->nextState('p3PromptTradeForWhite');
		$this->gamestate->nextState('chkLimitsP3');
	}
	
	function stchkLimitsP3()
	{
		// ${actplayer} can only have 5 white dices and 4 of every other color
		$this->gamestate->nextState('p3PromptDiscardDices');
		$this->gamestate->nextState('nextPlayer');
	}
	
	function stnextPlayer()
	{
		// 
		$this->gamestate->nextState('chkIsP1WonByActivePlayer');
	}
	
	

//////////////////////////////////////////////////////////////////////////////
//////////// Zombie
////////////

    /*
        zombieTurn:
        
        This method is called each time it is the turn of a player who has quit the game (= "zombie" player).
        You can do whatever you want in order to make sure the turn of this player ends appropriately
        (ex: pass).
        
        Important: your zombie code will be called when the player leaves the game. This action is triggered
        from the main site and propagated to the gameserver from a server, not from a browser.
        As a consequence, there is no current player associated to this action. In your zombieTurn function,
        you must _never_ use getCurrentPlayerId() or getCurrentPlayerName(), otherwise it will fail with a "Not logged" error message. 
    */

    function zombieTurn( $state, $activePlayer )
    {
    	$statename = $state['name'];
    	
        if ($state['type'] === "activeplayer") {
            switch ($statename) {
                default:
                    $this->gamestate->nextState( "zombiePass" );
                	break;
            }

            return;
        }

        if ($state['type'] === "multipleactiveplayer") {
            // Make sure player is in a non blocking status for role turn
            $this->gamestate->setPlayerNonMultiactive( $activePlayer, '' );
            
            return;
        }

        throw new feException( "Zombie mode not supported at this game state: ".$statename );
    }
    
///////////////////////////////////////////////////////////////////////////////////:
////////// DB upgrade
//////////

    /*
        upgradeTableDb:
        
        You don't have to care about this until your game has been published on BGA.
        Once your game is on BGA, this method is called everytime the system detects a game running with your old
        Database scheme.
        In this case, if you change your Database scheme, you just have to apply the needed changes in order to
        update the game database and allow the game to continue to run with your new version.
    
    */
    
    function upgradeTableDb( $from_version )
    {
        // $from_version is the current version of this game database, in numerical form.
        // For example, if the game was running with a release of your game named "140430-1345",
        // $from_version is equal to 1404301345
        
        // Example:
//        if( $from_version <= 1404301345 )
//        {
//            // ! important ! Use DBPREFIX_<table_name> for all tables
//
//            $sql = "ALTER TABLE DBPREFIX_xxxxxxx ....";
//            self::applyDbUpgradeToAllDB( $sql );
//        }
//        if( $from_version <= 1405061421 )
//        {
//            // ! important ! Use DBPREFIX_<table_name> for all tables
//
//            $sql = "CREATE TABLE DBPREFIX_xxxxxxx ....";
//            self::applyDbUpgradeToAllDB( $sql );
//        }
//        // Please add your future database scheme changes here
//
//


    }    
}
