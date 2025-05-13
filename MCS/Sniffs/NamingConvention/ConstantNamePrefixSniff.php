<?php
/**
 * Ensures that constant names are prefixed by Mi.
 *
 * @author  Manish Gour <manishgour@milople.com>
 * @link    https://github.com/manish-gour22/milople-coding-standard
 */
namespace ManishGour\MCS\Sniffs\NamingConventions;

use PHP_CodeSniffer\Files\File;
use PHP_CodeSniffer\Sniffs\Sniff;
use PHP_CodeSniffer\Util\Tokens;

class ConstantNamePrefixSniff implements Sniff
{
    /**
     * Returns an array of tokens this test wants to listen for.
     *
     * @return array
     */
    public function register()
    {
        return [
            T_STRING,
            T_CONST,
        ];
    }

    /**
     * Processes this test, when one of its tokens is encountered.
     *
     * @param \PHP_CodeSniffer\Files\File $phpcsFile The file being scanned.
     * @param int                         $stackPtr  The position of the current token in the
     *                                               stack passed in $tokens.
     *
     * @return void
     */
    public function process(File $phpcsFile, $stackPtr)
    {
        $tokens = $phpcsFile->getTokens();

        if ($tokens[$stackPtr]['code'] === T_CONST) {
            // This is a class constant.
            $constant = $phpcsFile->findNext(Tokens::$emptyTokens, ($stackPtr + 1), null, true);
            if ($constant === false) {
                return;
            }

            $constName = $tokens[$constant]['content'];

            if (substr($constName, 0, 2) !== 'MI') {
                $phpcsFile->addError(
                    'Constants must be prefixed with "MI"; found "%s"',
                    $stackPtr,
                    'WrongClassConstantName',
                    [$constName]
                );
            }

            return;
        }

        // Only interested in define statements now.
        if (strtolower($tokens[$stackPtr]['content']) !== 'define') {
            return;
        }

        // Make sure this is not a method call.
        $prev = $phpcsFile->findPrevious(T_WHITESPACE, ($stackPtr - 1), null, true);
        if ($tokens[$prev]['code'] === T_OBJECT_OPERATOR
            || $tokens[$prev]['code'] === T_DOUBLE_COLON
            || $tokens[$prev]['code'] === T_NULLSAFE_OBJECT_OPERATOR
        ) {
            return;
        }

        // If the next non-whitespace token after this token
        // is not an opening parenthesis then it is not a function call.
        $openBracket = $phpcsFile->findNext(Tokens::$emptyTokens, ($stackPtr + 1), null, true);
        if ($openBracket === false) {
            return;
        }

        // The next non-whitespace token must be the constant name.
        $constPtr = $phpcsFile->findNext(T_WHITESPACE, ($openBracket + 1), null, true);
        if ($tokens[$constPtr]['code'] !== T_CONSTANT_ENCAPSED_STRING) {
            return;
        }

        $constName = $tokens[$constPtr]['content'];

        // Check for constants like self::CONSTANT.
        $prefix   = '';
        $splitPos = strpos($constName, '::');
        if ($splitPos !== false) {
            $prefix    = substr($constName, 0, ($splitPos + 2));
            $constName = substr($constName, ($splitPos + 2));
        }

        // Strip namespace from constant like /foo/bar/CONSTANT.
        $splitPos = strrpos($constName, '\\');
        if ($splitPos !== false) {
            $prefix    = substr($constName, 0, ($splitPos + 1));
            $constName = substr($constName, ($splitPos + 1));
        }

        if (substr($constName, 0, 2) !== 'MI') {
            $phpcsFile->addError(
                'Constants must be prefixed with "MI"; found "%s"',
                $stackPtr,
                'WrongConstantName',
                [$constName]
            );
        }
    }
}
