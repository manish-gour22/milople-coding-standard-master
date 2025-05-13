<?php
/**
 * Checks that traits are prefixed by Mi.
 *
 * @author  Manish Gour <manishgour@milople.com>
 * @link    https://github.com/manish-gour22/milople-coding-standard
 */
namespace ManishGour\MCS\Sniffs\NamingConventions;

use PHP_CodeSniffer\Files\File;
use PHP_CodeSniffer\Sniffs\Sniff;

class TraitNamePrefixSniff implements Sniff
{
    /**
     * Registers the tokens that this sniff wants to listen for.
     *
     * @return int[]
     */
    public function register()
    {
        return [T_TRAIT];
    }

    /**
     * Processes this sniff, when one of its tokens is encountered.
     *
     * @param \PHP_CodeSniffer\Files\File $phpcsFile The file being scanned.
     * @param int                         $stackPtr  The position of the current token
     *                                               in the stack passed in $tokens.
     *
     * @return void
     */
    public function process(File $phpcsFile, $stackPtr)
    {
        $traitName = $phpcsFile->getDeclarationName($stackPtr);
        if ($traitName === null) {
            return;
        }

        if (substr($traitName, 0, 2) !== 'Mi') {
            $phpcsFile->addError(
                'Trait names must be prefixed with "Mi"; found "%s"',
                $stackPtr,
                'WrongTraitName',
                [$traitName]
            );
        }
    }
}
