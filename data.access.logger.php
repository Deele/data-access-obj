<?php

if (!class_exists('Logger')) {
	
	/**
	 * Logger
	 *
	 * Contains static methods for message logging to system and output
	 *
	 * @author Nils Lindenthaal <nils@dfworks.lv>
	 * @copyright 2013 Dragonfly Works, LLC
	 * @license MIT
	 * @package data-access-obj
	 * @link https://github.com/Deele/data-access-obj GitHub repo for full package
	 * 
	 * @todo documentation
	 */
	class Logger {
		
		private static function saveToLogFile($contents, $file, $clear = false) {
			if (is_writable(dirname($file)) || is_writable($file)) {
				$fh = fopen($file, ($clear ? 'w' : 'a'));
				fwrite($fh, $contents);
				fclose($fh);
				return true;
			}
		}
		
		public static function newMessage(
			$message,
			$output_if_no_file = false, 
			$message_file = 'messages_log.html'
		) {
			$date = date('Y.m.d. H:i:s');
			
			$log_message = '<!-- '.$date.' -->'.
				'<div class="message">'.
				'<h3>Message:</h3>'.
				'<p'.$message.'</p>'.
				'<hr /><br />'.
				'<br /></div>'."\n";
			$message_file = __DIR__.DIRECTORY_SEPARATOR.$message_file;
			if (!static::saveToLogFile($log_message, $message_file) && $output_if_no_file) {
				echo $log_message;
			}
		}
		
		public static function newExceptionMessage(
			Exception $exception, 
			$clear = false, 
			$output_if_no_file = false, 
			$error_file = 'exceptions_log.html'
		) {
			$message = $exception->getMessage();
			$code = $exception->getCode();
			$file = $exception->getFile();
			$line = $exception->getLine();
			$trace = str_replace("\n", '<br />', $exception->getTraceAsString());
			$date = date('Y.m.d. H:i:s');
			
			$log_message = '<!-- '.$date.' -->'.
				'<div class="message error">'.
				'<h3>Exception information:</h3>'.
				'<p><strong>Date:</strong> '.$date.'</p>'.
				'<p><strong>Message:</strong> '.$message.'</p>'.
				'<p><strong>Code:</strong> '.$code.'</p>'.
				'<p><strong>File:</strong> '.$file.'</p>'.
				'<p><strong>Line:</strong> '.$line.'</p>'.
				'<h3>Stack trace:</h3>'.
				'<pre>'.$trace.'</pre><br />'.
				'<hr /><br />'.
				'<br /></div>'."\n";
			$error_file = __DIR__.DIRECTORY_SEPARATOR.$error_file;
			if (!static::saveToLogFile($log_message, $error_file) && $output_if_no_file) {
				echo $log_message;
			}
		}
	}
}