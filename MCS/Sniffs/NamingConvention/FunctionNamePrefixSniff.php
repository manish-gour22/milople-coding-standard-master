<?php
/**
 * Ensures method names are correct.
 *
 * @author  Manish Gour <manishgour@milople.com>
 * @link    https://github.com/manish-gour22/milople-coding-standard
 */
namespace ManishGour\MCS\Sniffs\NamingConventions;

use PHP_CodeSniffer\Files\File;
use PHP_CodeSniffer\Standards\PEAR\Sniffs\NamingConventions\ValidFunctionNameSniff as PEARValidFunctionNameSniff;

class FunctionNamePrefixSniff extends PEARValidFunctionNameSniff
{
    /**
     * Processes the tokens within the scope.
     *
     * @param \PHP_CodeSniffer\Files\File $phpcsFile The file being processed.
     * @param int                         $stackPtr  The position where this token was
     *                                               found.
     * @param int                         $currScope The position of the current scope.
     *
     * @return void
     */
    protected function processTokenWithinScope(File $phpcsFile, $stackPtr, $currScope)
    {
        $tokens = $phpcsFile->getTokens();

        // Determine if this is a function which needs to be examined.
        $conditions = $tokens[$stackPtr]['conditions'];
        end($conditions);
        $deepestScope = key($conditions);
        if ($deepestScope !== $currScope) {
            return;
        }

        $methodName = $phpcsFile->getDeclarationName($stackPtr);
        if ($methodName === null) {
            // Ignore closures.
            return;
        }

        $className = $phpcsFile->getDeclarationName($currScope);
        if (isset($className) === false) {
            $className = '[Anonymous Class]';
        }

        $errorData = [$className.'::'.$methodName];

        $methodNameLc = strtolower($methodName);
        $classNameLc  = strtolower($className);

        // PHP4 constructors are allowed to break our rules.
        if ($methodNameLc === $classNameLc) {
            return;
        }

        // PHP4 destructors are allowed to break our rules.
        if ($methodNameLc === '_'.$classNameLc) {
            return;
        }

        $methodProps    = $phpcsFile->getMethodProperties($stackPtr);
        $scope          = $methodProps['scope'];
        $scopeSpecified = $methodProps['scope_specified'];

        if ($methodProps['scope'] === 'private') {
            $isPublic = false;
        } else {
            $isPublic = true;
        }

        $testMethodName = ltrim($methodName, '_');

        if (substr($testMethodName, 0, 2) !== 'mi') {
            $error = 'Method name "%s" should start with prefix "mi"';
            
            $phpcsFile->addError($error, $stackPtr, 'WrongMethodName', $errorData);
        }
    }

    /**
     * Processes the tokens outside the scope.
     *
     * @param \PHP_CodeSniffer\Files\File $phpcsFile The file being processed.
     * @param int                         $stackPtr  The position where this token was
     *                                               found.
     *
     * @return void
     */
    protected function processTokenOutsideScope(File $phpcsFile, $stackPtr)
    {
        $functionName = $phpcsFile->getDeclarationName($stackPtr);
        if ($functionName === null) {
            return;
        }

        $errorData = [$functionName];

        if (substr($functionName, 0, 2) !== 'mi') {
            $error = 'Function name "%s" should start with prefix "mi"';
            
            $phpcsFile->addError($error, $stackPtr, 'WrongMethodName', $errorData);
        }
    }
}
