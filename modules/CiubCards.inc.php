<?php

//namespace CiubGame;

// "fileID" => 0, "cardLetter" => "D", "isOwl" => false, "victoryPoints" => 5, "requiredDices" => "8"
class Card
{
    public $fileID;
    public $cardLetter;
    public $isOwl;
    public $victoryPoints;
    public $requiredDices;

    public function __construct($fileID)
    {
        $this->fileID = $fileID;
    }

    /**
     * @param Cube[] $cubes
     */
    public function isSolvedBy(array $cubes)
    {
        return CardSolver::Solve($cubes, $this->requiredDices);
    }
}

class CardSolver
{ 
    /**
     * @param Cube[] $cubes
     */
    public static function Solve(array $cubes, string $requiredDices)
    {
        $req = explode(',', $requiredDices);
        if (count($req) == 1)
        {
            $req = $req[0];
            if ($req[0] == 'x') // x4: 4 of the same number or 4 skulls
            {
                $reqNum = (int)$req[1];
                $valueCount = array_fill_keys([0, 1, 2, 3, 4, 5, 6, 7], 0);
                foreach ($cubes as $cube)
                {
                    $value = $cube->getFaceValue();
                    if ($value > 0)
                        $valueCount[$value]++;
                    elseif ($cube->getFace() == CubeFaces::Action_Skull)
                        $valueCount[0]++;
                }
                return count(array_filter($valueCount, function($num) use ($reqNum) { return $num >= $reqNum; })) > 0;
            }
            else // 20: "20+" card, sum of values must be greater or equal than 20
            {
                return array_reduce($cubes, function($sum, Cube $cube) { return $sum + $cube->getFaceValue(); }, 0) >= $req;
            }
        }
        else // "1,3,3,5": cube faces needs at least one 1, two 3 and one 5 faces
        {
            $values = array_map(function(Cube $cube) { return $cube->getFaceValue(); }, $cubes);
            $allFound = true;
            foreach ($req as $reqVal)
            {
                $key = array_search($reqVal, $values);
                if (false === $key)
                {
                    $allFound = false;
                    break;
                }
                unset($values[$key]);
            }
            return $allFound;
        }
    }
}

/*
// Test possible win conditions

require_once('CiubCube.inc.php');
$a = new PurpleCube;
$b = new GreenCube;
$c = new PurpleCube;
$d = new PurpleCube;
$a->setFace(CubeFaces::Action_Skull);
$c->setFace(CubeFaces::Numeric_3);
$d->setFace(CubeFaces::Numeric_3);
$b->setFace(CubeFaces::Action_Skull);

assert(CardSolver::Solve([$a, $b, $c, $d], '3,3') === true);
assert(CardSolver::Solve([$a, $b, $c, $d], '1,3,3,5') === false);
assert(CardSolver::Solve([$a, $b, $c, $d, $b], 'x3') === true);
assert(CardSolver::Solve([$a, $b, $c, $d, $b], 'x4') === false);

assert(CardSolver::Solve([$a, $b, $c, $d, $b], '10') === false);
assert(CardSolver::Solve([$a, $b, $c, $d, $b, $c, $d], '10'));
*/