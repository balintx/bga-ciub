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

/*
   Game state machine is a tool used to facilitate game developpement by doing common stuff that can be set up
   in a very easy way from this configuration file.

   Please check the BGA Studio presentation about game state to understand this, and associated documentation.

   Summary:

   States types:
   _ activeplayer: in this type of state, we expect some action from the active player.
   _ multipleactiveplayer: in this type of state, we expect some action from multiple players (the active players)
   _ game: this is an intermediary state where we don't expect any actions from players. Your game logic must decide what is the next game state.
   _ manager: special type for initial and final state

   Arguments of game states:
   _ name: the name of the GameState, in order you can recognize it on your own code.
   _ description: the description of the current game state is always displayed in the action status bar on
                  the top of the game. Most of the time this is useless for game state with "game" type.
   _ descriptionmyturn: the description of the current game state when it's your turn.
   _ type: defines the type of game states (activeplayer / multipleactiveplayer / game / manager)
   _ action: name of the method to call when this game state become the current game state. Usually, the
             action method is prefixed by "st" (ex: "stMyGameStateName").
   _ possibleactions: array that specify possible player actions on this step. It allows you to use "checkAction"
                      method on both client side (Javacript: this.checkAction) and server side (PHP: self::checkAction).
   _ transitions: the transitions are the possible paths to go from a game state to another. You must name
                  transitions in order to use transition names in "nextState" PHP method, and use IDs to
                  specify the next game state for each transition.
   _ args: name of the method to call to retrieve arguments for this gamestate. Arguments are sent to the
           client side to be used on "onEnteringState" or to set arguments in the gamestate description.
   _ updateGameProgression: when specified, the game progression is updated (=> call to your getGameProgression
                            method).
*/

//    !! It is not a good idea to modify this file when a game is running !!

 
$machinestates = array(

    // The initial state. Please do not modify.
    1 => array(
        "name" => "gameSetup",
        "description" => "",
        "type" => "manager",
        "action" => "stGameSetup",
        "transitions" => array( "" => 2 )
    ),
    
    /*
	// Note: ID=2 => your first state

    2 => array(
    		"name" => "playerTurn",
    		"description" => clienttranslate('${actplayer} must play a card or pass'),
    		"descriptionmyturn" => clienttranslate('${you} must play a card or pass'),
    		"type" => "activeplayer",
    		"possibleactions" => array( "playCard", "pass" ),
    		"transitions" => array( "playCard" => 2, "pass" => 2 )
    ),
    */
/*
    Examples:
    
    2 => array(
        "name" => "nextPlayer",
        "description" => '',
        "type" => "game",
        "action" => "stNextPlayer",
        "updateGameProgression" => true,   
        "transitions" => array( "endGame" => 99, "nextPlayer" => 10 )
    ),
    
    10 => array(
        "name" => "playerTurn",
        "description" => clienttranslate('${actplayer} must play a card or pass'),
        "descriptionmyturn" => clienttranslate('${you} must play a card or pass'),
        "type" => "activeplayer",
        "possibleactions" => array( "playCard", "pass" ),
        "transitions" => array( "playCard" => 2, "pass" => 2 )
    ), 

*/    

  2 => 
  array (
    'name' => 'chkIsP1WonByActivePlayer',
    'description' => clienttranslate('Checking if ${actplayer} has already won the Opus Magnum'),
    'descriptionmyturn' => clienttranslate('Checking if ${you} ha already won the Opus Magnum'),
    'type' => 'game',
    'transitions' => 
    array (
      'stGameEnd' => 99,
      'chkIsP1OpusAvailable' => 3,
    ),
    'action' => 'stChkIsP1WonByActivePlayer',
  ),
  3 => 
  array (
    'name' => 'chkIsP1OpusAvailable',
    'description' => clienttranslate('Checking if ${actplayer} can place a token or remove a card from the top row'),
    'descriptionmyturn' => clienttranslate('Checking if ${you} can place a token or remove a card from the top row'),
    'type' => 'game',
    'transitions' => 
    array (
      'p1PromptDecideAction' => 10,
      'p2Roll' => 20,
    ),
    'action' => 'stChkIsP1OpusAvailable',
  ),
  10 => 
  array (
    'name' => 'p1PromptDecideAction',
    'description' => clienttranslate('${actplayer} can place his/her token or remove one card from the top row'),
    'descriptionmyturn' => clienttranslate('${you} can place your token or remove one card from the top row'),
    'type' => 'activeplayer',
    'transitions' => 
    array (
      'p1PromptPlaceToken' => 11,
      'p1PromptRemoveTopCard' => 12,
      'p2Roll' => 20,
    ),
    'possibleactions' => 
    array (
      0 => 'jmp_p1PromptPlaceToken',
      1 => 'jmp_p1PromptRemoveTopCard',
      2 => 'jmp_skipP1',
    ),
  ),
  11 => 
  array (
    'name' => 'p1PromptPlaceToken',
    'description' => clienttranslate('${actplayer} places his/her token on a spell card'),
    'descriptionmyturn' => clienttranslate('${you} place your token on a spell card'),
    'type' => 'activeplayer',
    'transitions' => 
    array (
      'p1PromptDecideAction' => 10,
      'p2Roll' => 20,
    ),
    'possibleactions' => 
    array (
      0 => 'p1Undo',
      1 => 'p1DoPlaceToken',
    ),
  ),
  12 => 
  array (
    'name' => 'p1PromptRemoveTopCard',
    'description' => clienttranslate('${actplayer} removes a spell card from the top row'),
    'descriptionmyturn' => clienttranslate('${you} remove a spell card from the top row'),
    'type' => 'activeplayer',
    'transitions' => 
    array (
      'p1PromptDecideAction' => 10,
      'p2Roll' => 20,
    ),
    'possibleactions' => 
    array (
      0 => 'p1Undo',
      1 => 'p1DoRemoveTopCard',
    ),
  ),
  20 => 
  array (
    'name' => 'p2Roll',
    'description' => clienttranslate('${actplayer} is rolling dies that are not in his/her dice tray'),
    'descriptionmyturn' => clienttranslate('${you} i rolling dies that are not in your dice tray'),
    'type' => 'game',
    'transitions' => 
    array (
      'chkHasP2DiceActions' => 21,
    ),
    'action' => 'stP2Roll',
  ),
  21 => 
  array (
    'name' => 'chkHasP2DiceActions',
    'description' => clienttranslate('Checking if there are any active dies with actions'),
    'descriptionmyturn' => clienttranslate('Checking if there are any active dies with actions'),
    'type' => 'game',
    'transitions' => 
    array (
      'p2PromptChooseDiceActions' => 22,
      'chkHasP2ActiveSkulls' => 24,
    ),
    'action' => 'stChkHasP2DiceActions',
  ),
  22 => 
  array (
    'name' => 'p2PromptChooseDiceActions',
    'description' => clienttranslate('${actplayer} may modify his/her dice using dice actions'),
    'descriptionmyturn' => clienttranslate('${you} may modify your dice using dice actions'),
    'type' => 'activeplayer',
    'transitions' => 
    array (
      'chkHasP2ActiveSkulls' => 24,
      'p2DiceAction' => 23,
    ),
    'possibleactions' => 
    array (
      0 => 'jmp_skipP2DiceActionPhase',
      1 => 'jmp_p2DiceAction',
    ),
  ),
  23 => 
  array (
    'name' => 'p2DiceAction',
    'description' => clienttranslate('Preparing for the correspondent dice action'),
    'descriptionmyturn' => clienttranslate('Preparing for the correspondent dice action'),
    'type' => 'game',
    'transitions' => 
    array (
      'p2PromptDiceActionReRollDie' => 51,
      'p2PromptDiceActionSwapDie' => 53,
      'p2PromptDiceActionAdjustFace' => 55,
    ),
    'action' => 'stP2DiceAction',
  ),
  24 => 
  array (
    'name' => 'chkHasP2ActiveSkulls',
    'description' => clienttranslate('[arg]${actplayer} must place dices with skulls into his/her dice tray'),
    'descriptionmyturn' => clienttranslate('[arg]${you} must place dices with skulls into your dice tray'),
    'type' => 'game',
    'transitions' => 
    array (
      'chkAreP2AllDicesInDiceTray' => 59,
    ),
    'action' => 'stChkHasP2ActiveSkulls',
  ),
  26 => 
  array (
    'name' => 'p2PromptMustSaveDices',
    'description' => clienttranslate('${actplayer} must place at least one dice into his/her dice tray'),
    'descriptionmyturn' => clienttranslate('${you} must place at least one dice into your dice tray'),
    'type' => 'activeplayer',
    'transitions' => 
    array (
      'chkAreP2AllDicesInDiceTray' => 59,
    ),
    'possibleactions' => 
    array (
      0 => 'p2DoPutDicesInTray',
    ),
  ),
  27 => 
  array (
    'name' => 'p2PromptCanSaveDices',
    'description' => clienttranslate('${actplayer} may place dices into his/her dice tray'),
    'descriptionmyturn' => clienttranslate('${you} may place dices into your dice tray'),
    'type' => 'activeplayer',
    'transitions' => 
    array (
      'chkAreP2AllDicesInDiceTray' => 59,
      'p2Roll' => 20,
    ),
    'possibleactions' => 
    array (
      0 => 'p2DoPutDicesInTray',
      1 => 'jmp_p2Roll',
    ),
  ),
  31 => 
  array (
    'name' => 'chkCanP3CastSpell',
    'description' => clienttranslate('Checking if any spell cards can be won'),
    'descriptionmyturn' => clienttranslate('Checking if any spell cards can be won'),
    'type' => 'game',
    'transitions' => 
    array (
      'p3PromptSelectWonSpell' => 60,
      'p3PromptBonusDice' => 61,
    ),
    'action' => 'stChkCanP3CastSpell',
  ),
  32 => 
  array (
    'name' => 'p3RestoreDiceTray',
    'description' => clienttranslate('${actplayer} restores their dice tray and starts over'),
    'descriptionmyturn' => clienttranslate('${you} restore their dice tray and starts over'),
    'type' => 'game',
    'transitions' => 
    array (
      'chkCanP3CastSpell' => 31,
      'chkHasP3MoreThanFive' => 62,
    ),
    'action' => 'stP3RestoreDiceTray',
  ),
  34 => 
  array (
    'name' => 'chkCountP3Deck',
    'description' => clienttranslate('Checking the remaining amount of cards in the deck'),
    'descriptionmyturn' => clienttranslate('Checking the remaining amount of cards in the deck'),
    'type' => 'game',
    'transitions' => 
    array (
      'p3WinOpus' => 80,
      'p3PromptRefillBottomRow' => 81,
      'chkHasP3MoreThanFive' => 62,
    ),
    'action' => 'stChkCountP3Deck',
  ),
  50 => 
  array (
    'name' => 'chkMustP2SaveOneDice',
    'description' => clienttranslate('${actplayer} must save at least one dice if not saved yet or the Swap Die action was not used'),
    'descriptionmyturn' => clienttranslate('${you} must save at least one dice if not saved yet or the Swap Die action was not used'),
    'type' => 'game',
    'transitions' => 
    array (
      'p2PromptMustSaveDices' => 26,
      'p2PromptCanSaveDices' => 27,
    ),
    'action' => 'stChkMustP2SaveOneDice',
  ),
  51 => 
  array (
    'name' => 'p2PromptDiceActionReRollDie',
    'description' => clienttranslate('${actplayer} must choose a dice to re-roll'),
    'descriptionmyturn' => clienttranslate('${you} must choose a dice to re-roll'),
    'type' => 'activeplayer',
    'transitions' => 
    array (
      'p2PromptChooseDiceActions' => 22,
      'chkHasP2DiceActions' => 21,
    ),
    'possibleactions' => 
    array (
      0 => 'p2UndoDiceAction',
      1 => 'p2DoDiceActionReRollDie',
    ),
  ),
  53 => 
  array (
    'name' => 'p2PromptDiceActionSwapDie',
    'description' => clienttranslate('${actplayer} must choose a dice to replace ${color} dice'),
    'descriptionmyturn' => clienttranslate('${you} must choose a dice to replace ${color} dice'),
    'type' => 'activeplayer',
    'transitions' => 
    array (
      'p2PromptChooseDiceActions' => 22,
      'chkHasP2DiceActions' => 21,
    ),
    'possibleactions' => 
    array (
      0 => 'p2UndoDiceAction',
      1 => 'p2DoDiceActionSwapDie',
    ),
  ),
  55 => 
  array (
    'name' => 'p2PromptDiceActionAdjustFace',
    'description' => clienttranslate('${actplayer} must choose a dice to adjust'),
    'descriptionmyturn' => clienttranslate('${you} must choose a dice to adjust'),
    'type' => 'activeplayer',
    'transitions' => 
    array (
      'p2PromptChooseDiceActions' => 22,
      'p2PromptDiceActionAdjustFace2' => 56,
    ),
    'possibleactions' => 
    array (
      0 => 'p2UndoDiceAction',
      1 => 'jmp_p2PromptDiceActionAdjustFace2',
    ),
  ),
  56 => 
  array (
    'name' => 'p2PromptDiceActionAdjustFace2',
    'description' => clienttranslate('${actplayer} must adjust the ${color} dice'),
    'descriptionmyturn' => clienttranslate('${you} must adjust the ${color} dice'),
    'type' => 'activeplayer',
    'transitions' => 
    array (
      'p2PromptChooseDiceActions' => 22,
      'chkHasP2DiceActions' => 21,
    ),
    'possibleactions' => 
    array (
      0 => 'p2UndoDiceAction',
      1 => 'p2DoDiceActionAdjustFace',
    ),
  ),
  59 => 
  array (
    'name' => 'chkAreP2AllDicesInDiceTray',
    'description' => clienttranslate('Checking if all dices are saved or not'),
    'descriptionmyturn' => clienttranslate('Checking if all dices are saved or not'),
    'type' => 'game',
    'transitions' => 
    array (
      'chkMustP2SaveOneDice' => 50,
      'chkCanP3CastSpell' => 31,
    ),
    'action' => 'stChkAreP2AllDicesInDiceTray',
  ),
  60 => 
  array (
    'name' => 'p3PromptSelectWonSpell',
    'description' => clienttranslate('${actplayer} can cast a spell and gain that spell card'),
    'descriptionmyturn' => clienttranslate('${you} can cast a spell and gain that spell card'),
    'type' => 'activeplayer',
    'transitions' => 
    array (
      'p3PromptBonusDice' => 61,
      'chkCountP3Deck' => 34,
    ),
    'possibleactions' => 
    array (
      0 => 'jmp_skipP3SpellWin',
      1 => 'p3DoWinSpellCard',
    ),
  ),
  61 => 
  array (
    'name' => 'p3PromptBonusDice',
    'description' => clienttranslate('${actplayer} gains an extra dice for not casting any spells'),
    'descriptionmyturn' => clienttranslate('${you} gain an extra dice for not casting any spells'),
    'type' => 'activeplayer',
    'transitions' => 
    array (
      'chkCanP3Trade' => 64,
    ),
    'possibleactions' => 
    array (
      0 => 'p3DoGainBonusDice',
    ),
  ),
  62 => 
  array (
    'name' => 'chkHasP3MoreThanFive',
    'description' => clienttranslate('${actplayer} can only have 5 dices'),
    'descriptionmyturn' => clienttranslate('${you} can only have 5 dices'),
    'type' => 'game',
    'transitions' => 
    array (
      'p3PromptReduceToFive' => 63,
      'chkCanP3Trade' => 64,
    ),
    'action' => 'stChkHasP3MoreThanFive',
  ),
  63 => 
  array (
    'name' => 'p3PromptReduceToFive',
    'description' => clienttranslate('${actplayer} must give back ${count} dice(s)'),
    'descriptionmyturn' => clienttranslate('${you} must give back ${count} dice(s)'),
    'type' => 'activeplayer',
    'transitions' => 
    array (
      'chkCanP3Trade' => 64,
    ),
    'possibleactions' => 
    array (
      0 => 'p3DoReduceToFive',
    ),
  ),
  64 => 
  array (
    'name' => 'chkCanP3Trade',
    'description' => clienttranslate('Checking if there is at least one saved dice with "Trade 2 for 1" face'),
    'descriptionmyturn' => clienttranslate('Checking if there is at least one saved dice with "Trade 2 for 1" face'),
    'type' => 'game',
    'transitions' => 
    array (
      'chkCanP3TradeForWhite' => 66,
      'p3PromptWantTrade' => 65,
    ),
    'action' => 'stChkCanP3Trade',
  ),
  65 => 
  array (
    'name' => 'p3PromptWantTrade',
    'description' => clienttranslate('${actplayer} can trade one dice for two other dices'),
    'descriptionmyturn' => clienttranslate('${you} can trade one dice for two other dices'),
    'type' => 'activeplayer',
    'transitions' => 
    array (
      'chkCanP3TradeForWhite' => 66,
      'p3RestoreDiceTray' => 32,
      'chkCanP3Trade' => 64,
    ),
    'possibleactions' => 
    array (
      0 => 'jmp_skipP3Trade',
      1 => 'p3Undo',
      2 => 'p3DoTradeDices',
    ),
  ),
  66 => 
  array (
    'name' => 'chkCanP3TradeForWhite',
    'description' => clienttranslate('Checking if player has any non-white dices'),
    'descriptionmyturn' => clienttranslate('Checking if player has any non-white dices'),
    'type' => 'game',
    'transitions' => 
    array (
      'p3PromptTradeForWhite' => 67,
      'chkLimitsP3' => 68,
    ),
    'action' => 'stChkCanP3TradeForWhite',
  ),
  67 => 
  array (
    'name' => 'p3PromptTradeForWhite',
    'description' => clienttranslate('${actplayer} can trade any dice for white dice'),
    'descriptionmyturn' => clienttranslate('${you} can trade any dice for white dice'),
    'type' => 'activeplayer',
    'transitions' => 
    array (
      'p3RestoreDiceTray' => 32,
      'chkLimitsP3' => 68,
    ),
    'possibleactions' => 
    array (
      0 => 'p3Undo',
      1 => 'p3DoTradeForWhite',
    ),
  ),
  68 => 
  array (
    'name' => 'chkLimitsP3',
    'description' => clienttranslate('${actplayer} can only have 5 white dices and 4 of every other color'),
    'descriptionmyturn' => clienttranslate('${you} can only have 5 white dices and 4 of every other color'),
    'type' => 'game',
    'transitions' => 
    array (
      'p3PromptDiscardDices' => 69,
      'nextPlayer' => 100,
    ),
    'action' => 'stChkLimitsP3',
  ),
  69 => 
  array (
    'name' => 'p3PromptDiscardDices',
    'description' => clienttranslate('${actplayer} can have a maximum of ${count} ${color} dices'),
    'descriptionmyturn' => clienttranslate('${you} can have a maximum of ${count} ${color} dices'),
    'type' => 'activeplayer',
    'transitions' => 
    array (
      'p3RestoreDiceTray' => 32,
      'chkCanP3TradeForWhite' => 66,
      'chkLimitsP3' => 68,
    ),
    'possibleactions' => 
    array (
      0 => 'p3Undo',
      1 => 'jmp_chkCanP3TradeForWhite',
      2 => 'p3DoDiscardDices',
    ),
  ),
  80 => 
  array (
    'name' => 'p3WinOpus',
    'description' => clienttranslate('"${actplayer} wins the Opus Magnum'),
    'descriptionmyturn' => clienttranslate('"${you} win the Opus Magnum'),
    'type' => 'game',
    'transitions' => 
    array (
      'chkCountP3Deck' => 34,
    ),
    'action' => 'stP3WinOpus',
  ),
  81 => 
  array (
    'name' => 'p3PromptRefillBottomRow',
    'description' => clienttranslate('${actplayer} must move a spell card from the top row to the bottom row'),
    'descriptionmyturn' => clienttranslate('${you} must move a spell card from the top row to the bottom row'),
    'type' => 'activeplayer',
    'transitions' => 
    array (
      'chkHasP3MoreThanFive' => 62,
    ),
    'possibleactions' => 
    array (
      0 => 'p3DoRefillBottomAndTopRow',
    ),
  ),
  100 => 
  array (
    'name' => 'nextPlayer',
    'description' => '',
    'descriptionmyturn' => '',
    'type' => 'game',
    'transitions' => 
    array (
      'chkIsP1WonByActivePlayer' => 2,
    ),
    'action' => 'stNextPlayer',
  ),
   
    // Final state.
    // Please do not modify (and do not overload action/args methods).
    99 => array(
        "name" => "gameEnd",
        "description" => clienttranslate("End of game"),
        "type" => "manager",
        "action" => "stGameEnd",
        "args" => "argGameEnd"
    )

);








