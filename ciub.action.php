<?php
/**
 *------
 * BGA framework: © Gregory Isabelli <gisabelli@boardgamearena.com> & Emmanuel Colin <ecolin@boardgamearena.com>
 * Ciub implementation : © <Your name here> <Your email address here>
 *
 * This code has been produced on the BGA studio platform for use on https://boardgamearena.com.
 * See http://en.doc.boardgamearena.com/Studio for more information.
 * -----
 * 
 * ciub.action.php
 *
 * Ciub main action entry point
 *
 *
 * In this file, you are describing all the methods that can be called from your
 * user interface logic (javascript).
 *       
 * If you define a method "myAction" here, then you can call it from your javascript code with:
 * this.ajaxcall( "/ciub/ciub/myAction.html", ...)
 *
 */
  
  
  class action_ciub extends APP_GameAction
  { 
	/**
	 * @var Ciub $game
	 */
	//public $game; // TODO remove this, IDE stub!!
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
	$this->game->p1DoPlaceToken();
	self::ajaxResponse();
}

public function p1DoRemoveTopCard()
{
	self::setAjaxMode();
	$this->game->p1DoRemoveTopCard();
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
	$this->game->jmp_p2DiceAction();
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
	$this->game->p2DoDiceActionReRollDie();
	self::ajaxResponse();
}

public function p2DoDiceActionSwapDie()
{
	self::setAjaxMode();
	$this->game->p2DoDiceActionSwapDie();
	self::ajaxResponse();
}

public function jmp_p2PromptDiceActionAdjustFace2()
{
	self::setAjaxMode();
	$this->game->jmp_p2PromptDiceActionAdjustFace2();
	self::ajaxResponse();
}

public function p2DoDiceActionAdjustFace()
{
	self::setAjaxMode();
	$this->game->p2DoDiceActionAdjustFace();
	self::ajaxResponse();
}

public function p2DoPutDicesInTray()
{
	self::setAjaxMode();
	$this->game->p2DoPutDicesInTray();
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
	$this->game->p3DoWinSpellCard();
	self::ajaxResponse();
}

public function p3DoRefillBottomAndTopRow()
{
	self::setAjaxMode();
	$this->game->p3DoRefillBottomAndTopRow();
	self::ajaxResponse();
}

public function p3DoGainBonusDice()
{
	self::setAjaxMode();
	$this->game->p3DoGainBonusDice();
	self::ajaxResponse();
}

public function p3DoReduceToFive()
{
	self::setAjaxMode();
	$this->game->p3DoReduceToFive();
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
	$this->game->p3DoTradeDices();
	self::ajaxResponse();
}

public function p3DoTradeForWhite()
{
	self::setAjaxMode();
	$this->game->p3DoTradeForWhite();
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
	$this->game->p3DoDiscardDices();
	self::ajaxResponse();
}


  }
  

