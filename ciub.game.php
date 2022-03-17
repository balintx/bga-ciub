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
		$isShortGame = self::getGameStateValue('game_length') == 1;
		$numPlayers = self::getPlayersNumber();
		$totalDeck = count(DeckGenerator::$FullDeck) - array_sum(DeckGenerator::GetReductionAmount($numPlayers, $isShortGame));
		$total = $totalDeck + $numPlayers;
		$currentDeck = count(LocationDB::getItemsAt('deck', 'card'));
		if ($currentDeck > 0)
			return (int)(100 * ($totalDeck - $currentDeck) / $total);
		
		$opusWinner = str_replace('player_', '', LocationDB::getItemLocation('card', 0));
		$activePlayer = self::getActivePlayerId();

		if ($activePlayer == $opusWinner)
			return 100;

		$i = 0;
	
		$p = $activePlayer;
		do
		{
			$i++;
			$p = self::getPlayerBefore($p);
		} while ($p != $opusWinner && $i < 4);

		return (int)(100 * ($totalDeck + $i) / $total);
    }


//////////////////////////////////////////////////////////////////////////////
//////////// Utility functions
////////////

    /*
        In this space, you can put any utility methods useful for your game logic
    */

	function checkP1TopRowCard($card_id)
	{
		if (LocationDB::getItemLocation('card', $card_id) != 'row_top' || CardDB::hasToken($card_id))
			self::throwInvalidCardTarget();
	}

	function throwInvalidCardTarget()
	{
		throw new BgaUserException(self::_('You cannot target that card (it is not in the correct row or it has a token)'));
	}

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
			throw new BgaUserException(self::_('You cannot target that dice'));
	}

	function moveCard($card_id, $location)
	{
		//$previousLocation = LocationDB::getItemLocation('card', $location);
		LocationDB::setItemLocation($location, 'card', $card_id);
		self::notifyAllPlayers('cardMoved', '', ['card_id' => $card_id, /*'previous_location' => 'deck',*/ 'new_location' => $location]);
	}

	function moveCube($cube_id, $location)
	{
		//$previousLocation = LocationDB::getItemLocation('cube', $location);
		LocationDB::setItemLocation($location, 'cube', $cube_id);
		self::notifyAllPlayers('cubeMoved', '', ['cube_id' => $cube_id, /*'previous_location' => $previousLocation,*/ 'new_location' => $location]);
	}

	
	/**
	 * @param Cube[] $cubes 
	 * @return void
	 */
	function rerollCubes($cubes)
	{
		foreach ($cubes as $cube)
		{
			$cube->doRoll("bga_rand");
			self::sendCubeUpdate($cube, true);
		}
	}

	function sendCubeUpdate(Cube $cube, bool $isReroll)
	{
		CubeDB::updateDb($cube);
		self::notifyAllPlayers('cubeUpdated', '', [
			'action_active' => $cube->isActionActive(),
			'active' => $cube->isActive(),
			'face' => $cube->getFace(),
			'id' => $cube->getId(),
			'reroll' => $isReroll
		]);
	}

	function canPlayerWinCard($card_id)
	{
		$activePlayer = self::getActivePlayerId();
		$card = DeckGenerator::$FullDeck[$card_id] ?? false;
		$cubes = CubeDB::getCubesAt('dicetray_'.$activePlayer, true, true);

		return
			$card
			&& self::isValidCardLocation(
				LocationDB::getItemLocation('card', $card_id)
			)
			&& !CardDB::hasToken($card_id, $activePlayer)
			&& CardSolver::Solve($cubes, $card['requiredDices'])
		;
	}

	/**
	 * Checks whether the active player can win a spell card located at the specified location
	 * 
	 * @param mixed $location 
	 * @return bool 
	 */
	function isValidCardLocation($location)
	{
		// bottom row always valid
		if ($location == 'row_bottom')
			return true;

		// only bottom or top row can be valid
		if ($location != 'row_top')
			return false;
		
		// top row is valid only when the Opus Magnum has already been won by someone
		if (LocationDB::getItemLocation('card', 0) == 'deck')
			return false;

		return true;
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
				
				self::checkP1TopRowCard($card_id);

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

				self::checkP1TopRowCard($card_id);

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
	// todo arg_
	function jmp_p2DiceAction($cube_id)
	{
		self::checkAction('jmp_p2DiceAction');
		switch ($this->gamestate->state_id())
		{
			case 22: // p2PromptChooseDiceActions
				// ${you} may modify your dice using dice actions
				$activePlayer = $this->getActivePlayerId();
				self::checkCubeLocation($cube_id, ['player_'.$activePlayer]);
				$cube = CubeDB::getCubes([$cube_id])[$cube_id];
				if (!$cube->isActive())
					throw new BgaUserException(self::_("You cannot target that dice"));
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

			case 53: // p2PromptDiceActionSwapDie
				// ${you} must choose a dice to replace ${color} dice

			case 55: // p2PromptDiceActionAdjustFace
				// ${you} must choose a dice to adjust
				
			case 56: // p2PromptDiceActionAdjustFace2
				// ${you} must adjust the ${color} dice

				$activePlayer = $this->getActivePlayerId();
				$cubes = [];
				$cubes[] = LocationDB::getFirstItemAt('diceaction_initiator', 'cube');
				$cubes[] = LocationDB::getFirstItemAt('diceaction_target', 'cube');
				array_walk($cubes, function($cube_id) use ($activePlayer) {
					if ($cube_id) { self::moveCube($cube_id, 'player_'.$activePlayer); } });
				
				$this->gamestate->nextState('p2PromptChooseDiceActions');
			break;

			default:
				$stateInfo = $this->gamestate->state();
				$id = $this->gamestate->state_id();
				throw new BgaVisibleSystemException("Action 'p2UndoDiceAction' has been called from an invalid state ($id: ".$stateInfo['name'].")");
			break;
		}
	}

	function p2DoDiceActionReRollDie($targetDiceId)
	{
		self::checkAction('p2DoDiceActionReRollDie');
		switch ($this->gamestate->state_id())
		{
			case 51: // p2PromptDiceActionReRollDie
				// ${you} must choose a dice to re-roll
				$activePlayer = $this->getActivePlayerId();

				// the dice with the "Reroll Dice" action on it
				$initDiceId = LocationDB::getFirstItemAt('diceaction_initiator', 'cube');
				$dices = CubeDB::getCubes([$initDiceId, $targetDiceId]);
				$initDice = $dices[$initDiceId];
				$targetDice = $dices[$targetDiceId];

				self::checkCubeLocation($targetDiceId, ['player_'.$activePlayer, 'diceaction_initiator']);
				
				$initDice->setActionInactive();
				self::sendCubeUpdate($initDice, false);
				
				self::moveCube($targetDiceId, 'diceaction_target');
				$oldFace = $targetDice->getFace();
				self::rerollCubes([$targetDice]);

				self::moveCube($targetDice, 'player_'.$activePlayer);
				if ($initDiceId != $targetDiceId)
				{
					self::moveCube($initDice, 'player_'.$activePlayer);
					self::notifyAllPlayers('diceaction_reroll', clienttranslate('${player_name} used {cube_1} to reroll {cube_2} to {cube_3}'),
						[
							'player_id' => $activePlayer, 'player_name' => $this->getActivePlayerName(),
						 	'init_color' => $initDice->getColor(),
						 	'target_color' => $targetDice->getColor(),
							'target_faces' => ['old' => $oldFace, 'new' => $targetDice->getFace()]
						]
					);
				}
				else
				{
					self::notifyAllPlayers('diceaction_reroll_self', clienttranslate('${player_name} used {cube_1} to reroll itself to {cube_2}'),
					[
						'player_id' => $activePlayer, 'player_name' => $this->getActivePlayerName(),
					 	'target_color' => $initDice->getColor(),
						'target_face' => $targetDice->getFace()
					]
				);
				}
				$this->gamestate->nextState('chkHasP2DiceActions');
			break;

			default:
				$stateInfo = $this->gamestate->state();
				$id = $this->gamestate->state_id();
				throw new BgaVisibleSystemException("Action 'p2DoDiceActionReRollDie' has been called from an invalid state ($id: ".$stateInfo['name'].")");
			break;
		}
	}

	function p2DoDiceActionSwapDie($targetDiceId)
	{
		self::checkAction('p2DoDiceActionSwapDie');
		switch ($this->gamestate->state_id())
		{
			case 53: // p2PromptDiceActionSwapDie
				// ${you} must choose a dice to replace ${color} dice
				$activePlayer = $this->getActivePlayerId();

				// the dice with the "Swap Die" action on it
				$initDiceId = LocationDB::getFirstItemAt('diceaction_initiator', 'cube');
				$dices = CubeDB::getCubes([$initDiceId, $targetDiceId]);
				$initDice = $dices[$initDiceId];
				$targetDice = $dices[$targetDiceId];

				self::checkCubeLocation($targetDice->getId(), ['summarycard']);
				if ($initDice->isSameColor($targetDice))
					throw new BgaUserException(self::_("You have to pick a dice with a different color"));
				
				$initDice->setActionInactive();
				$initDice->setActive();
				self::sendCubeUpdate($initDice, false);

				self::moveCube($initDiceId, 'summarycard');
				self::moveCube($targetDiceId, 'player_'.$activePlayer);
				$targetDice->setInactive();
				$targetDice->setActionInactive();
				self::sendCubeUpdate($targetDice, false);
				PlayerDB::mustSave($activePlayer, false);
				self::notifyAllPlayers('diceaction_swap', clienttranslate('${player_name} has swapped {cube_1} to a {color} cube'),
					[
						'player_id' => $activePlayer, 'player_name' => $this->getActivePlayerName(),
					 	'cube_color' => $initDice->getColor(),
						'color' => $this->cubeStrings['colors'][$targetDice->getColor()],
						'i18n' => ['color']
					]
				);
				$this->gamestate->nextState('chkHasP2DiceActions');
			break;

			default:
				$stateInfo = $this->gamestate->state();
				$id = $this->gamestate->state_id();
				throw new BgaVisibleSystemException("Action 'p2DoDiceActionSwapDie' has been called from an invalid state ($id: ".$stateInfo['name'].")");
			break;
		}
	}

	function jmp_p2PromptDiceActionAdjustFace2($targetDiceId)
	{
		self::checkAction('jmp_p2PromptDiceActionAdjustFace2');
		switch ($this->gamestate->state_id())
		{
			case 55: // p2PromptDiceActionAdjustFace
				// ${you} must choose a dice to adjust
				$activePlayer = $this->getActivePlayerId();

				// the dice with the "Adjust Face" action on it
				$initDiceId = LocationDB::getFirstItemAt('diceaction_initiator', 'cube');
				$dices = CubeDB::getCubes([$initDiceId, $targetDiceId]);
				$initDice = $dices[$initDiceId];
				$targetDice = $dices[$targetDiceId];
				self::checkCubeLocation($targetDiceId, ['player_'.$activePlayer, 'diceaction_initiator']);
				
				self::moveCube($targetDiceId, 'diceaction_target');
				

				$this->gamestate->nextState('p2PromptDiceActionAdjustFace2');
			break;

			default:
				$stateInfo = $this->gamestate->state();
				$id = $this->gamestate->state_id();
				throw new BgaVisibleSystemException("Action 'jmp_p2PromptDiceActionAdjustFace2' has been called from an invalid state ($id: ".$stateInfo['name'].")");
			break;
		}
	}
	// todo arg_ valid adjusts
	// todo set mustsave
	function p2DoDiceActionAdjustFace($face)
	{
		self::checkAction('p2DoDiceActionAdjustFace');
		switch ($this->gamestate->state_id())
		{
			case 56: // p2PromptDiceActionAdjustFace2
				// ${you} must adjust the ${color} dice
				$activePlayer = $this->getActivePlayerId();

				// the dice with the "Adjust Face" action on it
				$initDiceId = LocationDB::getFirstItemAt('diceaction_initiator', 'cube');
				$targetDiceId = LocationDB::getFirstItemAt('diceaction_target', 'cube');
				if (!$initDiceId)
					$initDiceId = $targetDiceId;
				
				$dices = CubeDB::getCubes([$initDiceId, $targetDiceId]);
				$initDice = $dices[$initDiceId];
				$targetDice = $dices[$targetDiceId];

				if (!$targetDice->isValidAdjust($face, $initDice))
					throw new BgaUserException("You cannot adjust to that face");

				$initDice->setActionInactive();
				self::sendCubeUpdate($initDice, false);

				$targetDice->setFace($face);
				$targetDice->setActive();
				$targetDice->setActionActive();
				self::sendCubeUpdate($targetDice, false);

				if ($initDiceId == $targetDiceId)
				{
					self::moveCube($targetDice, 'dicetray_'.$activePlayer);
					self::notifyAllPlayers('diceaction_adjust_self', clienttranslate('${player_name} has adjusted {cube_1} to {cube_2} and put it in his/her dice tray'),
						[
							'player_id' => $activePlayer, 'player_name' => $this->getActivePlayerName(),
							'cube_color' => $initDice->getColor(),
							'face' => $targetDice->getFace()
						]
					);
				}
				else
				{
					self::moveCube($targetDice, 'player_'.$activePlayer);
					self::moveCube($initDice, 'player_'.$activePlayer);
					self::notifyAllPlayers('diceaction_adjust', clienttranslate('${player_name} has adjusted {cube_1} to {cube_2} and put ${cube_3} in his/her dice tray'),
						[
							'player_id' => $activePlayer, 'player_name' => $this->getActivePlayerName(),
							'cube_color' => $initDice->getColor(),
							'face' => $targetDice->getFace()
						]
					);
				}

				$this->gamestate->nextState('chkHasP2DiceActions');
			break;

			default:
				$stateInfo = $this->gamestate->state();
				$id = $this->gamestate->state_id();
				throw new BgaVisibleSystemException("Action 'p2DoDiceActionAdjustFace' has been called from an invalid state ($id: ".$stateInfo['name'].")");
			break;
		}
	}
	// todo p2roll send list to client log
	// todo preserve sent cubes (util for color and face?)
	function p2DoPutDicesInTray($diceIds)
	{
		self::checkAction('p2DoPutDicesInTray');
		
		switch ($this->gamestate->state_id())
		{
			case 26: // p2PromptMustSaveDices
				// ${you} must place at least one dice into your dice tray

			case 27: // p2PromptCanSaveDices
				// ${you} may place dices into your dice tray		

				$activePlayer = self::getActivePlayerId();
				$diceIds = explode(' ', $diceIds);
				$dices = CubeDB::getCubes($diceIds);
				$valid = false;
				foreach ($dices as $dice)
				{
					if (!$dice->isActive())
						throw new BgaUserException(self::_('You cannot target that dice'));

					self::checkCubeLocation($dice->getId(), 'player_'.$activePlayer);
					self::moveCube($dice->getId(), 'dicetray_'.$activePlayer);
					$valid = true;
				
				}
				if (!$valid)
					throw new BgaUserException(self::_('Please select at least one dice'));

				PlayerDB::mustSave($activePlayer, false);

				self::notifyAllPlayers('dicetraySaved', clienttranslate('${player_name} has placed ${dices} in his/her dice tray'),
					[
						'player_id' => $activePlayer,
						'player_name' => $this->getActivePlayerName(),
						'cubes' => $diceIds // todo
					]
				);
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
				self::notifyAllPlayers('cardwinSkip', clienttranslate('${player_name} has chosen not to win a spell card'), ['player_name' => $this->getActivePlayerName(), 'player_id' => $this->getActivePlayerId()]);

				$this->gamestate->nextState('p3PromptBonusDice');
			break;

			default:
				$stateInfo = $this->gamestate->state();
				$id = $this->gamestate->state_id();
				throw new BgaVisibleSystemException("Action 'jmp_skipP3SpellWin' has been called from an invalid state ($id: ".$stateInfo['name'].")");
			break;
		}
	}
	// todo arg_ winnablespellcards
	function p3DoWinSpellCard($card_id)
	{
		self::checkAction('p3DoWinSpellCard');
		switch ($this->gamestate->state_id())
		{
			case 60: // p3PromptSelectWonSpell
				// ${you} can cast a spell and gain that spell card
				$activePlayer = self::getActivePlayerId();

				// todo hack hastoken to allow player
				if (!self::canPlayerWinCard($card_id))
					self::throwInvalidCardTarget();

				self::moveCard($card_id, 'player_'.$activePlayer);
				self::notifyAllPlayers('cardWon', clienttranslate('${player_name} has won ${spell_card}'),
					[
						'player_name' => $this->getActivePlayerName(),
						'player_id' => $activePlayer,
						'card_id' => $card_id
					]
				);
				PlayerDB::wonCard($activePlayer, true);
				$this->gamestate->nextState('chkCountP3Deck');
			break;

			default:
				$stateInfo = $this->gamestate->state();
				$id = $this->gamestate->state_id();
				throw new BgaVisibleSystemException("Action 'p3DoWinSpellCard' has been called from an invalid state ($id: ".$stateInfo['name'].")");
			break;
		}
	}

	function arg_RefillBottomRow()
	{
		return LocationDB::getItemsAt('row_top', 'card');
	}

	function p3DoRefillBottomAndTopRow($card_id)
	{
		self::checkAction('p3DoRefillBottomAndTopRow');
		switch ($this->gamestate->state_id())
		{
			case 81: // p3PromptRefillBottomRow
				// ${you} must move a spell card from the top row to the bottom row
				if (LocationDB::getItemLocation('card', $card_id) != 'row_top')
					throw new BgaUserException(self::_('You cannot target that card (it is not in the top row)'));

				self::moveCard($card_id, 'row_bottom');
				self::notifyAllPlayers('bottomRefilled', clienttranslate('${player_name} has moved ${card} from the top to the bottom row'),
					['player_name' => self::getActivePlayerName(), 'player_id' => self::getActivePlayerId(), 'card_id' => $card_id]);

				self::refillTopRow();

				$this->gamestate->nextState('chkHasP3MoreThanFive');
			break;

			default:
				$stateInfo = $this->gamestate->state();
				$id = $this->gamestate->state_id();
				throw new BgaVisibleSystemException("Action 'p3DoRefillBottomAndTopRow' has been called from an invalid state ($id: ".$stateInfo['name'].")");
			break;
		}
	}

	function p3DoGainBonusDice($cube_id)
	{
		self::checkAction('p3DoGainBonusDice');
		switch ($this->gamestate->state_id())
		{
			case 61: // p3PromptBonusDice
				// ${you} gain an extra dice for not casting any spells
				self::checkCubeLocation($cube_id, ['summarycard']);
				$activePlayer = self::getActivePlayerId();

				self::moveCube($cube_id, 'player_'.$activePlayer);
				self::notifyAllPlayers('bonusDice', clienttranslate('${player_name} has received a bonus ${color} dice'),
					['player_name' => self::getActivePlayerName(), 'player_id' => self::getActivePlayerId(), 'color' => $cube_id[5], 'i18n' => ['color']]);

				$this->gamestate->nextState('chkCanP3Trade');
			break;

			default:
				$stateInfo = $this->gamestate->state();
				$id = $this->gamestate->state_id();
				throw new BgaVisibleSystemException("Action 'p3DoGainBonusDice' has been called from an invalid state ($id: ".$stateInfo['name'].")");
			break;
		}
	}

	function arg_ReduceToFive()
	{
		$activePlayer = self::getActivePlayerId();
		return count(LocationDB::getItemsAt('player_'.$activePlayer)) + count(LocationDB::getItemsAt('dicetray_'.$activePlayer)) - 5;
	}

	function p3DoReduceToFive($cube_ids)
	{
		self::checkAction('p3DoReduceToFive');
		switch ($this->gamestate->state_id())
		{
			case 63: // p3PromptReduceToFive
				// ${you} must give back ${count} dice(s)
				$cubeIds = explode(' ', $cube_ids);
				$expectedReduce = self::arg_ReduceToFive();
				if (count($cubeIds) != $expectedReduce)
					throw new BgaUserException(sprintf(self::_("You must select exactly %d cubes to reduce your cubes to five"), $expectedReduce));

				$activePlayer = self::getActivePlayerId();

				foreach ($cubeIds as $id)
				{
					self::checkCubeLocation($id, ['player_'.$activePlayer, 'dicetray_'.$activePlayer]);
					self::moveCube($id, 'summarycard');
				}

				self::notifyAllPlayers('reduce', clienttranslate('${player_name} has placed ${dice} back to the summary card'),
					['player_name' => self::getActivePlayerName(), 'player_id' => $activePlayer, 'cube_ids' => $cubeIds]);

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
				if (count(self::arg_tradeForWhite()) == 0 || count(self::arg_availableWhites()) == 0)
					throw new BgaUserException(self::_('You cannot trade any dices at the moment'));

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

	function p3DoTradeDices($dice_ids)
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

	function p3DoTradeForWhite($cube_ids)
	{
		self::checkAction('p3DoTradeForWhite');
		switch ($this->gamestate->state_id())
		{
			case 67: // p3PromptTradeForWhite
				// ${you} can trade any dice for white dice
				$cubeIds = explode(' ', $cube_ids);
				if (count($cubeIds) == 0)
					throw new BgaUserException(self::_('Please select at least one dice'));

				$activePlayer = self::getActivePlayerId();

				$whites = self::arg_availableWhites();
				$logWhites = $whites;
				if (count($whites) < count($cubeIds))
					throw new BgaUserException(sprintf(self::_('You can only select a maximum of %d dice(s) at this time (amount of white dices on the summary card)'), count($whites)));

				foreach ($cubeIds as $id)
				{
					self::checkCubeLocation($id, ['player_'.$activePlayer, 'dicetray_'.$activePlayer]);
					if ($id[5] == CubeColors::White)
						throw new BgaUserException(self::_('You cannot target that dice'));

					$white = array_pop($whites);
					self::moveCube($id, 'summarycard');
					self::moveCube($white, 'player_'.$activePlayer);
				}

				self::notifyAllPlayers('trade', clienttranslate('${player_name} has traded ${dice} for ${dice2}'),
					['player_name' => self::getActivePlayerName(), 'player_id' => $activePlayer, 'cube_ids' => $cubeIds, 'cube_ids2' => $logWhites]);

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
				$whites = LocationDB::getItemsAt('summarycard', 'cube');
				$whites = array_filter($whites, function ($cube_id) { return $cube_id[5] == CubeColors::White; });
				//if (count($whites))
				$this->gamestate->nextState('chkCanP3TradeForWhite');
			break;

			default:
				$stateInfo = $this->gamestate->state();
				$id = $this->gamestate->state_id();
				throw new BgaVisibleSystemException("Action 'jmp_chkCanP3TradeForWhite' has been called from an invalid state ($id: ".$stateInfo['name'].")");
			break;
		}
	}

	function p3DoDiscardDices($cube_ids)
	{
		self::checkAction('p3DoDiscardDices');
		switch ($this->gamestate->state_id())
		{
			case 69: // p3PromptDiscardDices
				// ${you} can have a maximum of ${count} ${color} dices
				$cubeIds = explode(' ', $cube_ids);
				$activePlayer = self::getActivePlayerId();

				foreach ($cubeIds as $id)
				{
					self::checkCubeLocation($id, ['player_'.$activePlayer, 'dicetray_'.$activePlayer]);
					self::moveCube($id, 'summarycard');
				}

				self::notifyAllPlayers('reduce', clienttranslate('${player_name} has placed ${dice} back to the summary card'),
					['player_name' => self::getActivePlayerName(), 'player_id' => $activePlayer, 'cube_ids' => $cubeIds]);

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

		PlayerDB::wonCard($activePlayer, false);
		foreach (LocationDB::getItemsAt('dicetray_'.$activePlayer, 'cube') as $cube_id)
		{
			self::moveCube($cube_id, 'player_'.$activePlayer);
		}

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
		PlayerDB::mustSave($activePlayer, true);
		$cubes = CubeDB::getCubesAt('player_'.$activePlayer, true, true);

		self::rerollCubes($cubes);

		self::notifyAllPlayers('dicesRerolled', clienttranslate('${player_name} rerolls dices not in his/her dice tray'),
			['player_name' => $this->getActivePlayerName(), 'player_id' => $activePlayer]);
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
			self::notifyAllPlayers('skullsMoved', clienttranslate('${player_name} places ${count} skull(s) into his/her dice tray'), ['player_name' => $this->getActivePlayerName(), 'count' => count($cubes)]);
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
		if (PlayerDB::mustSave(self::getActivePlayerId()))
			$this->gamestate->nextState('p2PromptMustSaveDices');
		else
			$this->gamestate->nextState('p2PromptCanSaveDices');
	}
	
	function stchkCanP3CastSpell()
	{
		// Checking if any spell cards can be won
		if (count(self::arg_p3PromptSelectWonSpell()) > 0)
			$this->gamestate->nextState('p3PromptSelectWonSpell');
		else
			$this->gamestate->nextState('p3PromptBonusDice');
	}

	function arg_p3PromptSelectWonSpell()
	{
		$cardIds = CardDB::getCardsAt('row_bottom');
		if (self::isValidCardLocation('row_top'))
			$cardIds = array_merge($cardIds, CardDB::getCardsAt('row_top'));

		$cardIds = array_filter($cardIds, "Ciub::canPlayerWinCard");
		return $cardIds;
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
				if (count(self::arg_RefillBottomRow()) > 0)
					$this->gamestate->nextState('p3PromptRefillBottomRow');
				else
					$this->gamestate->nextState('chkHasP3MoreThanFive');
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

		self::notifyAllPlayers('opusWon', clienttranslate('${player_name} has won the Opus Magnum'), ['player_id' => $activePlayer, 'player_name' => $this->getActivePlayerName()]);
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
		$activePlayer = self::getActivePlayerId();
		
		$original = PlayerDB::getSavedCubes($activePlayer);

		foreach (LocationDB::getItemsAt('dicetray_'.$activePlayer, 'cube') as $id)
		{
			if (!in_array($id, $original))
				self::moveCube($id, 'summarycard');
		}

		foreach (LocationDB::getItemsAt('player_'.$activePlayer, 'cube') as $id)
		{
			if (!in_array($id, $original))
				self::moveCube($id, 'summarycard');
		}

		foreach ($original as $id)
		{
			if (LocationDB::getItemLocation('cube', $id) != 'dicetray_'.$activePlayer)
				self::moveCube($id, 'dicetray_'.$activePlayer);
		}
		
		self::notifyAllPlayers('diceTrayRestore', 
			clienttranslate('${actplayer} restores their dice tray and starts over'), ['player_name' => self::getActivePlayerName(), 'player_id' => $activePlayer]);
		
		if (PlayerDB::wonCard($activePlayer))
			$this->gamestate->nextState('chkHasP3MoreThanFive');
		else
			$this->gamestate->nextState('chkCanP3CastSpell');	
	}
	
	function stchkCanP3TradeForWhite()
	{
		// Checking if player has any non-white dices
		if (count(self::arg_tradeForWhite()) > 0 && count(self::arg_availableWhites()) > 0)
			$this->gamestate->nextState('p3PromptTradeForWhite');
		else
			$this->gamestate->nextState('chkLimitsP3');
	}

	function arg_availableWhites()
	{
		return
			array_filter(
				LocationDB::getItemsAt('summarycard', 'cube'),
				function ($cube_id) {
					return $cube_id[5] == CubeColors::White;
				}
			)
		;
	}
	
	function arg_tradeForWhite()
	{
		$activePlayer = self::getActivePlayerId();
		$locationSQL = "'player_$activePlayer', 'dicetray_$activePlayer'";
		$whiteSQL = 'cube_' . CubeColors::White . '_%';

		return self::getObjectListFromDB(
			"SELECT cubes.id FROM cubes
				INNER JOIN item_locations
				ON item_locations.id = cubes.id
				WHERE item_locations.location IN ($locationSQL)
				AND item_locations.item_id LIKE '$whiteSQL'"
		);
		
	}
	
	function stchkLimitsP3()
	{
		// ${actplayer} can only have 5 white dices and 4 of every other color
		if (self::arg_limitBreach())
			$this->gamestate->nextState('p3PromptDiscardDices');
		else
			$this->gamestate->nextState('nextPlayer');
	}

	function arg_limitBreach()
	{
		$activePlayer = self::getActivePlayerId();
		$diceIds = array_merge(LocationDB::getItemsAt('player_'.$activePlayer), LocationDB::getItemsAt('dicetray_'.$activePlayer));
		$colors = [];
		foreach ($diceIds as $id)
		{
			$color = $id[5];
			$colors[$color] = ($colors[$color] ?? 0) + 1;
			if ($color == CubeColors::White)
			{
				if ($colors[$color] > 5)
					return $color;
			}
			else
			{
				if ($colors[$color] > 4)
					return $color;
			}
		}

		return false;
	}
	
	function stnextPlayer()
	{
		$this->activeNextPlayer();
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
