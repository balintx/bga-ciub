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
  
  
  class action_ciub extends APP_GameAction
  { 
	/**
	 * @var Ciub $game
	 */
	public $game; // TODO remove this, IDE stub!!
    // Constructor: please do not modify
   	public function __default()
  	{
  	    if( self::isArg( 'notifwindow') )
  	    {
            $this->view = "common_notifwindow";
  	        $this->viewArgs['table'] = self::getArg( "table", AT_posint, true );
  	    }
  	    else
  	    {
            $this->view = "ciub_ciub";
            self::trace( "Complete reinitialization of board game" );
      }
  	} 
  	
  	// TODO: defines your action entry points there


    /*
    
    Example:
  	
    public function myAction()
    {
        self::setAjaxMode();     

        // Retrieve arguments
        // Note: these arguments correspond to what has been sent through the javascript "ajaxcall" method
        $arg1 = self::getArg( "myArgument1", AT_posint, true );
        $arg2 = self::getArg( "myArgument2", AT_posint, true );

        // Then, call the appropriate method in your game logic, like "playCard" or "myAction"
        $this->game->myAction( $arg1, $arg2 );

        self::ajaxResponse( );
    }
    
    */

public function jmp_p1PromptPlaceToken()
{
	self::setAjaxMode();
	$this->game->jmp_p1PromptPlaceToken();
	self::ajaxResponse();
}

public function jmp_p1PromptRemoveTopCard()
{
	self::setAjaxMode();
	$this->game->jmp_p1PromptRemoveTopCard();
	self::ajaxResponse();
}

public function jmp_skipP1()
{
	self::setAjaxMode();
	$this->game->jmp_skipP1();
	self::ajaxResponse();
}

public function p1Undo()
{
	self::setAjaxMode();
	$this->game->p1Undo();
	self::ajaxResponse();
}

public function p1DoPlaceToken()
{
	self::setAjaxMode();
	$card_id = self::getArg( "card_id", AT_posint, true );
	$this->game->p1DoPlaceToken($card_id);
	self::ajaxResponse();
}

public function p1DoRemoveTopCard()
{
	self::setAjaxMode();
	$card_id = self::getArg( "card_id", AT_posint, true );
	$this->game->p1DoRemoveTopCard($card_id);
	self::ajaxResponse();
}

public function jmp_skipP2DiceActionPhase()
{
	self::setAjaxMode();
	$this->game->jmp_skipP2DiceActionPhase();
	self::ajaxResponse();
}

public function jmp_p2DiceAction()
{
	self::setAjaxMode();
	$cube_id = self::getArg( "cube_id", AT_alphanum, true );
	$this->game->jmp_p2DiceAction($cube_id);
	self::ajaxResponse();
}

public function p2UndoDiceAction()
{
	self::setAjaxMode();
	$this->game->p2UndoDiceAction();
	self::ajaxResponse();
}

public function p2DoDiceActionReRollDie()
{
	self::setAjaxMode();
	$cube_id = self::getArg( "cube_id", AT_alphanum, true );
	$this->game->p2DoDiceActionReRollDie($cube_id);
	self::ajaxResponse();
}

public function p2DoDiceActionSwapDie()
{
	self::setAjaxMode();
	$cube_id = self::getArg( "cube_id", AT_alphanum, true );
	$this->game->p2DoDiceActionSwapDie($cube_id);
	self::ajaxResponse();
}

public function jmp_p2PromptDiceActionAdjustFace2()
{
	self::setAjaxMode();
	$cube_id = self::getArg( "cube_id", AT_alphanum, true );
	$this->game->jmp_p2PromptDiceActionAdjustFace2($cube_id);
	self::ajaxResponse();
}

public function p2DoDiceActionAdjustFace()
{
	self::setAjaxMode();
	$face = self::getArg( "face", AT_posint, true );
	$this->game->p2DoDiceActionAdjustFace($face);
	self::ajaxResponse();
}

public function p2DoPutDicesInTray()
{
	self::setAjaxMode();
	$cube_ids = self::getArg( "cube_ids", AT_alphanum, true );
	$this->game->p2DoPutDicesInTray($cube_ids);
	self::ajaxResponse();
}

public function jmp_p2Roll()
{
	self::setAjaxMode();
	$this->game->jmp_p2Roll();
	self::ajaxResponse();
}

public function jmp_skipP3SpellWin()
{
	self::setAjaxMode();
	$this->game->jmp_skipP3SpellWin();
	self::ajaxResponse();
}

public function p3DoWinSpellCard()
{
	self::setAjaxMode();
	$card_id = self::getArg( "card_id", AT_posint, true );
	$this->game->p3DoWinSpellCard($card_id);
	self::ajaxResponse();
}

public function p3DoRefillBottomAndTopRow()
{
	self::setAjaxMode();
	$card_id = self::getArg( "card_id", AT_posint, true );
	$this->game->p3DoRefillBottomAndTopRow($card_id);
	self::ajaxResponse();
}

public function p3DoGainBonusDice()
{
	self::setAjaxMode();
	$cube_id = self::getArg( "cube_id", AT_alphanum, true );
	$this->game->p3DoGainBonusDice($cube_id);
	self::ajaxResponse();
}

public function p3DoReduceToFive()
{
	self::setAjaxMode();
	$cube_ids = self::getArg( "cube_ids", AT_alphanum, true );
	$this->game->p3DoReduceToFive($cube_ids);
	self::ajaxResponse();
}

public function jmp_skipP3Trade()
{
	self::setAjaxMode();
	$this->game->jmp_skipP3Trade();
	self::ajaxResponse();
}

public function p3Undo()
{
	self::setAjaxMode();
	$this->game->p3Undo();
	self::ajaxResponse();
}

public function p3DoTradeDices()
{
	self::setAjaxMode();
	$cube_ids = self::getArg( "cube_ids", AT_alphanum, true );
	$this->game->p3DoTradeDices($cube_ids);
	self::ajaxResponse();
}

public function p3DoTradeForWhite()
{
	self::setAjaxMode();
	$cube_ids = self::getArg( "cube_ids", AT_alphanum, true );
	$this->game->p3DoTradeForWhite($cube_ids);
	self::ajaxResponse();
}

public function jmp_chkCanP3TradeForWhite()
{
	self::setAjaxMode();
	$this->game->jmp_chkCanP3TradeForWhite();
	self::ajaxResponse();
}

public function p3DoDiscardDices()
{
	self::setAjaxMode();
	$cube_ids = self::getArg( "cube_ids", AT_alphanum, true );
	$this->game->p3DoDiscardDices($cube_ids);
	self::ajaxResponse();
}


  }
  

