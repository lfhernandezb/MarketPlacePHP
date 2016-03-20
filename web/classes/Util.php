<?php
	class Util {
		public static function write_to_log($message, $level=E_USER_NOTICE) {
			$caller = next(debug_backtrace());
			trigger_error($message.' in <strong>'.$caller['function'].'</strong> called from <strong>'.$caller['file'].'</strong> on line <strong>'.$caller['line'].'</strong>'."\n<br />error handler", $level);
		}
	}