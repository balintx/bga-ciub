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
//namespace CiubGame\DeckGenerator;

class DeckGenerator
{
	/**
	 * @var array $FullDeck array of associative arrays of all cards
	 */
	static $FullDeck = [["fileID" => 0, "cardLetter" => "D", "isOwl" => false, "victoryPoints" => 5, "requiredDices" => "8"], ["fileID" => 1, "cardLetter" => "A", "isOwl" => false, "victoryPoints" => 3, "requiredDices" => "x4"], ["fileID" => 2, "cardLetter" => "A", "isOwl" => false, "victoryPoints" => 3, "requiredDices" => "2,4,6"], ["fileID" => 3, "cardLetter" => "A", "isOwl" => false, "victoryPoints" => 3, "requiredDices" => 20], ["fileID" => 4, "cardLetter" => "A", "isOwl" => false, "victoryPoints" => 4, "requiredDices" => "2,2,2,2"], ["fileID" => 5, "cardLetter" => "A", "isOwl" => false, "victoryPoints" => 3, "requiredDices" => "1,3,3,5"], ["fileID" => 6, "cardLetter" => "A", "isOwl" => false, "victoryPoints" => 4, "requiredDices" => "6,6"], ["fileID" => 7, "cardLetter" => "A", "isOwl" => false, "victoryPoints" => 3, "requiredDices" => "2,2,4,4"], ["fileID" => 8, "cardLetter" => "A", "isOwl" => false, "victoryPoints" => 4, "requiredDices" => "1,4,7"], ["fileID" => 9, "cardLetter" => "A", "isOwl" => false, "victoryPoints" => 3, "requiredDices" => "1,1,1"], ["fileID" => 10, "cardLetter" => "A", "isOwl" => false, "victoryPoints" => 4, "requiredDices" => "3,3,3,3"], ["fileID" => 11, "cardLetter" => "B", "isOwl" => false, "victoryPoints" => 6, "requiredDices" => "6,6,6"], ["fileID" => 12, "cardLetter" => "B", "isOwl" => false, "victoryPoints" => 5, "requiredDices" => "1,1,3,5,5"], ["fileID" => 13, "cardLetter" => "B", "isOwl" => false, "victoryPoints" => 6, "requiredDices" => "1,3,5,7"], ["fileID" => 14, "cardLetter" => "B", "isOwl" => false, "victoryPoints" => 5, "requiredDices" => "27"], ["fileID" => 15, "cardLetter" => "B", "isOwl" => false, "victoryPoints" => 5, "requiredDices" => "x5"], ["fileID" => 16, "cardLetter" => "B", "isOwl" => false, "victoryPoints" => 5, "requiredDices" => "2,2,4,4,6"], ["fileID" => 17, "cardLetter" => "B", "isOwl" => true, "victoryPoints" => 4, "requiredDices" => "1,1,1,1"], ["fileID" => 18, "cardLetter" => "B", "isOwl" => true, "victoryPoints" => 6, "requiredDices" => "3,3,3,3,3"], ["fileID" => 19, "cardLetter" => "B", "isOwl" => true, "victoryPoints" => 6, "requiredDices" => "2,2,2,2,2"], ["fileID" => 20, "cardLetter" => "B", "isOwl" => true, "victoryPoints" => 5, "requiredDices" => "2,3,4,5,6"], ["fileID" => 21, "cardLetter" => "B", "isOwl" => true, "victoryPoints" => 6, "requiredDices" => "1,1,6,6"], ["fileID" => 22, "cardLetter" => "C", "isOwl" => false, "victoryPoints" => 7, "requiredDices" => "2,2,4,4,6,6"], ["fileID" => 23, "cardLetter" => "C", "isOwl" => false, "victoryPoints" => 8, "requiredDices" => "33"], ["fileID" => 24, "cardLetter" => "C", "isOwl" => false, "victoryPoints" => 8, "requiredDices" => "6,6,6,6,6"], ["fileID" => 25, "cardLetter" => "C", "isOwl" => false, "victoryPoints" => 7, "requiredDices" => "1,1,1,3,3,3"], ["fileID" => 26, "cardLetter" => "C", "isOwl" => false, "victoryPoints" => 7, "requiredDices" => "7,7,7"], ["fileID" => 27, "cardLetter" => "C", "isOwl" => false, "victoryPoints" => 6, "requiredDices" => "1,2,3,4,5,6"], ["fileID" => 28, "cardLetter" => "C", "isOwl" => true, "victoryPoints" => 6, "requiredDices" => "1,1,3,3,5,5"], ["fileID" => 29, "cardLetter" => "C", "isOwl" => true, "victoryPoints" => 8, "requiredDices" => "1,1,1,1,6,6"], ["fileID" => 30, "cardLetter" => "C", "isOwl" => true, "victoryPoints" => 7, "requiredDices" => "6,6,6,6"], ["fileID" => 31, "cardLetter" => "C", "isOwl" => true, "victoryPoints" => 7, "requiredDices" => "x6"], ["fileID" => 32, "cardLetter" => "C", "isOwl" => true, "victoryPoints" => 6, "requiredDices" => "30"]];

	/**
	*	Returns an array of shuffled cards (a deck) based on the number of players and the gametype
	*
	*	@param int $numPlayers the number of players in the game (2, 3, 4)
	*	@param bool $isShortGame flag for normal or short game
	*	@param callable|null $deckShufflerFunction a function which returns the array passed as argument, with the values being in randomised order
	*	@param callable|null $rngFunction a function that can take two parameters (min, max) and return a random integer number N (min <= N <= max)
	*
	*	@return array Shuffled cards, needs array_pop() to draw from the deck
	*/
	static function Generate($numPlayers, $isShortGame, $deckShufflerFunction = NULL, $rngFunction = NULL)
	{
		if (!is_callable($rngFunction))
			$rngFunction = 'mt_rand';

		if ($numPlayers < 2 || $numPlayers > 4)
		{
			throw new \Exception("cannot generate deck for $numPlayers player(s), valid numPlayers is between 2 and 4");
		}
		
		/**
		 * Rule book:
		 * @var array $deck "Sort the spell cards according to the letters on their backs (A-D)."
		 */

		$deck['A'] = self::GetFilteredDeckByLetter('A', !$isShortGame);
		$deck['B'] = self::GetFilteredDeckByLetter('B', !$isShortGame);
		$deck['C'] = self::GetFilteredDeckByLetter('C', !$isShortGame);
		$deck['D'] = self::GetFilteredDeckByLetter('D', !$isShortGame);
		
		/**
		 * "With fewer than 4 players, randomly remove some cards from the game:"
		 *
		 * @var int[] $reduceBy First we determine how many cards will need to be removed per card letter
		*/
		$reduceBy = self::GetReductionAmount($numPlayers, $isShortGame);
		
		/**
		 * Then reduce the different card groups randomly by the required amount
		 */
		self::RandomReduceArrayByNumberOfElements($deck['A'], $reduceBy['A'], $rngFunction);
		self::RandomReduceArrayByNumberOfElements($deck['B'], $reduceBy['B'], $rngFunction);
		self::RandomReduceArrayByNumberOfElements($deck['C'], $reduceBy['C'], $rngFunction);
		/**
		 * "Then, shuffle each group of cards (A, B, C) separately."
		 */

		// Lambda function for swap-based shuffling in case none was provided
		if (!is_callable($deckShufflerFunction))
			$deckShufflerFunction = function($deck) use ($rngFunction) {
				$deckMaxIdx = count($deck) - 1;
				// loop through all indexes, swap with a random idx (or itself)
				for ($pos1 = 0; $pos1 < $deckMaxIdx; $pos1++) {
					$card1 = $deck[$pos1];
					$pos2 = $rngFunction(0, $deckMaxIdx);
					$card2 = $deck[$pos2];
					$deck[$pos1] = $card2;
					$deck[$pos2] = $card1;
				}
				return $deck;
			};
			
		// "Put the single D card *face down* on the table. Place the C cards on top of it and the B cards on top of these to form the draw pile."
		// "The A cards are placed on the table *face up* in two equal rows."
		//
		// For now, we will also include "A" cards on "top" of the deck (to draw from the deck, we need to pop the array)
		//var_dump(count($deck['D']), count($deckShufflerFunction($deck['C'])), count($deckShufflerFunction($deck['B'])), count($deckShufflerFunction($deck['A'])));
		return array_merge($deck['D'], $deckShufflerFunction($deck['C']), $deckShufflerFunction($deck['B']), $deckShufflerFunction($deck['A']));
	}
	
	/**
	*	Returns an array with key-value pairs where the key is a card letter (A, B, C) and value is
	*		the required amount of cards that should be removed from that letter group deck
	*
	*
	*	@param int $numPlayers the number of players in the game
	*	@param bool $isShortGame flag for normal or short game
	*
	*/
	static function GetReductionAmount($numPlayers, $isShortGame)
	{
		switch ($numPlayers)
		{
			case 4:
				return ['A' => 0, 'B' => 0, 'C' => 0];
			break;
			case 3:
				if ($isShortGame)
					return ['A' => 2, 'B' => 1, 'C' => 1];
				else
					return ['A' => 2, 'B' => 2, 'C' => 2];
			break;
			case 2:
				if ($isShortGame)
					return ['A' => 4, 'B' => 2, 'C' => 2];
				else
					return ['A' => 4, 'B' => 4, 'C' => 4];
			break;
			default:
				throw new \Exception("invalid number of players: $numPlayers");
			break;
		}
	}
	
	/**
	*	Return a subset of all cards based on the card letter.
	*
	*	@param string $cardLetter
	*   - the card group (the letter on the back on the card)
	*   - possible values: 'A', 'B', 'C', 'D'
	*	@param bool $includeOwls whether to include cards with an Owl on them or not (short vs long game)
	*
	*/
	static function GetFilteredDeckByLetter($cardLetter, $includeOwls)
	{
		return array_values( // reindex
			array_filter(self::$FullDeck, new DeckFilter($cardLetter, $includeOwls))
		);
	}
	
	/**
	*	Randomly removes $subject number of elements from $subject array
	*
	*	@param array &$subject the array to be reduced
	*	@param int $reduceCount the number of elements this function needs to remove
	*	@param callable $rngFunction a function that can take two parameters (min, max) and return a random integer number N (min <= N <= max)
	*
	*/
	static function RandomReduceArrayByNumberOfElements(&$subject, $reduceCount, $rngFunction)
	{
		$subjectCount = count($subject);
		if ($reduceCount == 0)
			return;
		
		if ($reduceCount < 0)
			throw new \Exception("array cannot be reduced by negative elements");
			
		if ($reduceCount >= $subjectCount)
		{
			$subject = [];
			return;
		}
		
		// randomly generate keys until enough keys are collected to be removed
		/* 
		* [note from the developer]
		*
		* Alternative approach could be:
		* loop $reduceCount times, each time remove a random element, reindex array
		* which have the benefit of running O(N)
		* however reindexing the array (in order to effectively be able to select a random element)
		* multiple times can get slower than just letting it work out itself randomly
		*
		*/
		
		$keysToRemove = [];
		do
		{
			$keysToRemove[] = $rngFunction(0, $subjectCount - 1);
			$keysToRemove = array_unique($keysToRemove, SORT_REGULAR); // remove duplicates
		} while (count($keysToRemove) < $reduceCount);
		
		foreach ($keysToRemove as $keyIndex)
		{
			unset($subject[$keyIndex]);
		}
		
		$subject = array_values($subject); // reindex array
	}
}

/**
 * A helper class for DeckGenerator class to aid array_filter()
 */
class DeckFilter
{
	public $cardLetter;
	public $includeOwls;
	
	function __construct($cardLetter, $includeOwls)
	{
		$this->cardLetter = $cardLetter;
		$this->includeOwls = $includeOwls;
	}
	
	function __invoke($card)
	{
		return $card['cardLetter'] === $this->cardLetter && ($this->includeOwls || !$card['isOwl']);
	}
}

// manual tests

//var_dump(DeckGenerator::Generate(3, false));