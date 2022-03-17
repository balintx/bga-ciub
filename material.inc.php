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

Example:

$this->card_types = array(
    1 => array( "card_name" => ...,
                ...
              )
);

*/

require_once('modules/CiubDecks.inc.php');
require_once('modules/CiubCubes.inc.php');
require_once('modules/CiubCards.inc.php');
require_once('modules/CiubDB.inc.php');

$this->cubeStrings = [
  'colors' => [
    CubeColors::Blue => clienttranslate('Blue'),
    CubeColors::Green => clienttranslate('Green'),
    CubeColors::Orange => clienttranslate('Orange'),
    CubeColors::Pink => clienttranslate('Pink'),
    CubeColors::Purple => clienttranslate('Purple'),
    CubeColors::White => clienttranslate('White'),
    CubeColors::Yellow => clienttranslate('Yellow')
  ],
  'faces' => [
    CubeFaces::Action_Adjust => clienttranslate('Adjust Face'),
    CubeFaces::Action_Reroll => clienttranslate('Re-Roll Die'),
    CubeFaces::Action_Skull => clienttranslate('Skull'),
    CubeFaces::Action_Swap => clienttranslate('Swap Die'),
    CubeFaces::Action_Trade => clienttranslate('2 for 1')
  ]
];

