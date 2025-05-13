<?php
/**
 * Checks the naming of variables and member variables.
 *
 * @author  Manish Gour <manishgour@milople.com>
 * @link    https://github.com/manish-gour22/milople-coding-standard
 */
namespace ManishGour\MCS\Sniffs\NamingConventions;

use PHP_CodeSniffer\Files\File;
use PHP_CodeSniffer\Sniffs\AbstractVariableSniff;
use PHP_CodeSniffer\Util\Tokens;

class VariableNamePrefixSniff extends AbstractVariableSniff
{
    /**
     * Processes this test, when one of its tokens is encountered.
     *
     * @param \PHP_CodeSniffer\Files\File $phpcsFile The file being scanned.
     * @param int                         $stackPtr  The position of the current token in the
     *                                               stack passed in $tokens.
     *
     * @return void
     */
    protected function processVariable(File $phpcsFile, $stackPtr)
    {
        $tokens  = $phpcsFile->getTokens();
        $varName = ltrim($tokens[$stackPtr]['content'], '$');

        // If it's a php reserved var, then its ok.
        if (isset($this->phpReservedVars[$varName]) === true) {
            return;
        }

        $objOperator = $phpcsFile->findNext([T_WHITESPACE], ($stackPtr + 1), null, true);
        if ($tokens[$objOperator]['code'] === T_OBJECT_OPERATOR
            || $tokens[$objOperator]['code'] === T_NULLSAFE_OBJECT_OPERATOR
        ) {
            // Check to see if we are using a variable from an object.
            $var = $phpcsFile->findNext([T_WHITESPACE], ($objOperator + 1), null, true);
            if ($tokens[$var]['code'] === T_STRING) {
                $bracket = $phpcsFile->findNext([T_WHITESPACE], ($var + 1), null, true);
                if ($tokens[$bracket]['code'] !== T_OPEN_PARENTHESIS) {
                    $objVarName = $tokens[$var]['content'];

                    // There is no way for us to know if the var is public or
                    // private, so we have to ignore a leading underscore if there is
                    // one and just check the main part of the variable name.
                    $originalVarName = $objVarName;
                    if (substr($objVarName, 0, 1) === '_') {
                        $objVarName = substr($objVarName, 1);
                    }

                    if (substr($objVarName, 0, 2) !== 'mi') {
                        $error = 'Member variable "%s" should start with prefix "mi"';
                        $data  = [$originalVarName];
                        
                        $phpcsFile->addError($error, $var, 'WrongMemberVariableName', $data);
                    }
                }
            }
        }

        $objOperator = $phpcsFile->findPrevious(T_WHITESPACE, ($stackPtr - 1), null, true);
        if ($tokens[$objOperator]['code'] === T_DOUBLE_COLON) {
            // The variable lives within a class, and is referenced like
            // this: MyClass::$_variable, so we don't know its scope.
            $objVarName = $varName;
            if (substr($objVarName, 0, 1) === '_') {
                $objVarName = substr($objVarName, 1);
            }

            if (substr($objVarName, 0, 2) !== 'mi') {
                $error = 'Member variable "%s" should start with prefix "mi"';
                $data  = [$tokens[$stackPtr]['content']];
                
                $phpcsFile->addError($error, $stackPtr, 'WrongMemberVariableName', $data);
            }

            return;
        }

        // There is no way for us to know if the var is public or private,
        // so we have to ignore a leading underscore if there is one and just
        // check the main part of the variable name.
        $originalVarName = $varName;
        if (substr($varName, 0, 1) === '_') {
            $inClass = $phpcsFile->hasCondition($stackPtr, Tokens::$ooScopeTokens);
            if ($inClass === true) {
                $varName = substr($varName, 1);
            }
        }

        if (substr($varName, 0, 2) !== 'mi') {
            $error = 'Variable "%s" should start with prefix "mi"';
            $data  = [$originalVarName];
            
            $phpcsFile->addError($error, $stackPtr, 'WrongVariableName', $data);
        }
    }

    /**
     * Processes class member variables.
     *
     * @param \PHP_CodeSniffer\Files\File $phpcsFile The file being scanned.
     * @param int                         $stackPtr  The position of the current token in the
     *                                               stack passed in $tokens.
     *
     * @return void
     */
    protected function processMemberVar(File $phpcsFile, $stackPtr)
    {
        $tokens = $phpcsFile->getTokens();

        $varName     = ltrim($tokens[$stackPtr]['content'], '$');
        $memberProps = $phpcsFile->getMemberProperties($stackPtr);
        if (empty($memberProps) === true) {
            // Couldn't get any info about this variable, which
            // generally means it is invalid or possibly has a parse
            // error. Any errors will be reported by the core, so
            // we can ignore it.
            return;
        }

        $errorData = [$varName];

        // Remove a potential underscore prefix for testing CamelCaps.
        $varName = ltrim($varName, '_');

        if (substr($varName, 0, 2) !== 'mi') {
            $error = 'Member variable "%s" should start with prefix "mi"';
            
            $phpcsFile->addError($error, $stackPtr, 'WrongVariableName', $errorData);
        }
    }

    /**
     * Processes the variable found within a double quoted string.
     *
     * @param \PHP_CodeSniffer\Files\File $phpcsFile The file being scanned.
     * @param int                         $stackPtr  The position of the double quoted
     *                                               string.
     *
     * @return void
     */
    protected function processVariableInString(File $phpcsFile, $stackPtr)
    {
        $tokens = $phpcsFile->getTokens();

        if (preg_match_all(
            '|[^\\\]\${?([a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*)|',
            $tokens[$stackPtr]['content'],
            $matches
        ) !== 0) {
            foreach ($matches[1] as $varName) {
                // If it's a php reserved var, then its ok.
                if (isset($this->phpReservedVars[$varName]) === true) {
                    continue;
                }

                if (substr($varName, 0, 2) !== 'mi') {
                    $error = 'Member variable "%s" should start with prefix "mi"';
                    $data  = [$varName];

                    $phpcsFile->addError($error, $stackPtr, 'WrongVariableName', $data);
                }
            }
        }
    }
}
