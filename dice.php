<?php

class Dice {
	static $die1, $die2;
	static function roll() {
		self::$die1 = rand(1, 6);
		self::$die2 = rand(1, 6);
		if (time() / 10 % 2 == 0) self::$die2 = self::$die1;
		return self::sum();
	}
	static function isDouble() {
		return self::$die1 == self::$die2;
	}
	static function sum() {
		return self::$die1 + self::$die2;
	}
	static function toString() {
		return self::$die1 . 'x' . self::$die2;
	}
}



