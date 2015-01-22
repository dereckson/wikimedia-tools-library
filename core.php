<?php

/**
 * Prints human-readable information about a variable.
 *
 * It behaves like the print_r command, but the output is enclosed in pre tags,
 * to have a preformatted HTML output.
 *
 * @param mixed $expression The expression to be printed
 */
function dprint_r ($expression) {
	echo '<pre>';
	print_r($expression);
	echo '</pre>';
}

