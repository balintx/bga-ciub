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

class CubeFactory
{
    public static function createFromId($item_id)
    {
        list($type, $color, $id) = explode('_', $item_id);
        if ($type != 'cube')
            throw new \Exception("item type '$type' unknown for ".__CLASS__ ." (expected: 'cube')");

        $color = filter_var($color, FILTER_SANITIZE_NUMBER_INT);
        $id = filter_var($id, FILTER_SANITIZE_NUMBER_INT);

        switch ($color)
        {
            case CubeColors::Blue:
                $cube = new BlueCube;
            break;
            case CubeColors::Green:
                $cube = new GreenCube;
            break;
            case CubeColors::Orange:
                $cube = new OrangeCube;
            break;
            case CubeColors::Pink:
                $cube = new PinkCube;
            break;
            case CubeColors::Purple:
                $cube = new PurpleCube;
            break;
            case CubeColors::White:
                $cube = new WhiteCube;
            break;
            case CubeColors::Yellow:
                $cube = new YellowCube;
            break;
            default:
                throw new \Exception("Unknown color ($color) for cube '$item_id'");
            break;
        }
        $cube->setId('cube_'.$color.'_'.$id);
        return $cube;
    }
}

class Cube
{
    protected $face;
    protected $color;
    protected $allFaces;
    protected $isActionActive;
    protected $isActive;
    protected $id;

    public function getId()
    {
        return $this->id;
    }

    public function getFace()
    {
        return $this->face;
    }

    public function getColor()
    {
        return $this->color;
    }

    public function getAllFaces()
    {
        return $this->allFaces;
    }

    public function getFaceValue()
    {
        return $this->face < 8 ? $this->face : 0;
    }

    public function isActionActive()
    {
        return $this->isActionActive;
    }

    public function isActive()
    {
        return $this->isActive;
    }

    public function isSameColor($cube)
    {
        return $this->getColor() === $cube->getColor();
    }

    public function isWhite()
    {
        return $this->getColor() === CubeColors::White;
    }

    public function __construct($allFaces)
    {
        if (count($allFaces) != 6)
        {
            throw new \BgaVisibleSystemException("Cube::_construct() expects an array with the 6 possible sides");
        }
        $this->allFaces = $allFaces;
        $this->setFace($allFaces[0]);
        $this->setActionInactive();
        $this->setInactive();
    }

    public function doRoll($rngFunction = "mt_rand")
    {
        $this->setActive();
        $this->setActionActive();
        return $this->setFace($this->allFaces[$rngFunction(0,5)]);
    }

    public function setFace($face)
    {
        if (!in_array($face, $this->allFaces))
        {
            throw new \BgaVisibleSystemException("Unknown face given for Cube::setFace ($face)");
        }
        $this->face = $face;
        return $this;
    }

    public function setId($id)
    {
        $this->id = $id;
        return $this;
    }

    public function setColor($color)
    {
        $this->color = $color;
        return $this;
    }

    public function setActionActive()
    {
        $this->isActionActive = true;
        return $this;
    }

    public function setActionInactive()
    {
        $this->isActionActive = false;
        return $this;
    }

    public function setActive()
    {
        $this->isActive = true;
        return $this;
    }

    public function setInactive()
    {
        $this->isActive = false;
        return $this;
    }

    public function __toString()
    {
        return json_encode([
            'face' => [
                'current' => $this->getFace(),
                'all' => $this->getAllFaces()
            ],
            'color' => $this->getColor(),
            'active' => $this->isActive(),
            'actionactive' => $this->isActionActive(),
            'value' => $this->getFaceValue()
            ]
        );
    }
}

final class CubeFaces
{
    const Numeric_1 = 1;
    const Numeric_2 = 2;
    const Numeric_3 = 3;
    const Numeric_4 = 4;
    const Numeric_5 = 5;
    const Numeric_6 = 6;
    const Numeric_7 = 7;
    const Action_Skull = 8;
    const Action_Trade = 9;
    const Action_Swap = 10;
    const Action_Reroll = 11;
    const Action_Adjust = 12;
}

final class CubeColors
{
    const White = 1;
    const Green = 2;
    const Pink = 3;
    const Orange = 4;
    const Yellow = 5;
    const Purple = 6;
    const Blue = 7;
}

class WhiteCube extends Cube
{
    public function __construct()
    {
        parent::__construct([
            CubeFaces::Action_Swap,
            CubeFaces::Action_Swap,
            CubeFaces::Numeric_1,
            CubeFaces::Numeric_2,
            CubeFaces::Numeric_3,
            CubeFaces::Numeric_4
        ]);
        $this->setColor(CubeColors::White);
    }
}

class GreenCube extends Cube
{
    public function __construct()
    {
        parent::__construct([
            CubeFaces::Action_Skull,
            CubeFaces::Action_Reroll,
            CubeFaces::Action_Reroll,
            CubeFaces::Action_Trade,
            CubeFaces::Action_Trade,
            CubeFaces::Numeric_7
        ]);
        $this->setColor(CubeColors::Green);
    }
}

class PinkCube extends Cube
{
    public function __construct()
    {
        parent::__construct([
            CubeFaces::Numeric_3,
            CubeFaces::Numeric_4,
            CubeFaces::Numeric_4,
            CubeFaces::Numeric_5,
            CubeFaces::Numeric_5,
            CubeFaces::Numeric_6,
        ]);
        $this->setColor(CubeColors::Pink);
    }
}

class OrangeCube extends Cube
{
    public function __construct()
    {
        parent::__construct([
            CubeFaces::Numeric_2,
            CubeFaces::Numeric_2,
            CubeFaces::Numeric_2,
            CubeFaces::Numeric_4,
            CubeFaces::Numeric_4,
            CubeFaces::Numeric_6,
        ]);
        $this->setColor(CubeColors::Orange);
    }
}

class YellowCube extends Cube
{
    public function __construct()
    {
        parent::__construct([
            CubeFaces::Numeric_1,
            CubeFaces::Numeric_1,
            CubeFaces::Numeric_3,
            CubeFaces::Numeric_3,
            CubeFaces::Numeric_5,
            CubeFaces::Numeric_5,
        ]);
        $this->setColor(CubeColors::Yellow);
    }
}

class PurpleCube extends Cube
{
    public function __construct()
    {
        parent::__construct([
            CubeFaces::Action_Skull,
            CubeFaces::Action_Adjust,
            CubeFaces::Action_Adjust,
            CubeFaces::Action_Trade,
            CubeFaces::Numeric_3,
            CubeFaces::Numeric_4,
        ]);
        $this->setColor(CubeColors::Purple);
    }
}

class BlueCube extends Cube
{
    public function __construct()
    {
        parent::__construct([
            CubeFaces::Action_Skull,
            CubeFaces::Action_Reroll,
            CubeFaces::Action_Swap,
            CubeFaces::Action_Adjust,
            CubeFaces::Numeric_1,
            CubeFaces::Numeric_2,
        ]);
        $this->setColor(CubeColors::Blue);
    }
}

/*
// Test CubeFactory
assert(CubeFactory::createFromId('cube_'.CubeColors::Orange.'_3') instanceof OrangeCube);
assert(CubeFactory::createFromId('cube_'.CubeColors::Pink.'_3') instanceof PinkCube);
assert(CubeFactory::createFromId('cube_'.CubeColors::Blue.'_3') instanceof BlueCube);
assert(CubeFactory::createFromId('cube_'.CubeColors::Green.'_3') instanceof GreenCube);
assert(CubeFactory::createFromId('cube_'.CubeColors::Purple.'_3') instanceof PurpleCube);
assert(CubeFactory::createFromId('cube_'.CubeColors::Yellow.'_3') instanceof YellowCube);
assert(CubeFactory::createFromId('cube_'.CubeColors::White.'_3') instanceof WhiteCube);
*/