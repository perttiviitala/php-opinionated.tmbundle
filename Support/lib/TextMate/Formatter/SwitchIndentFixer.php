<?php

declare(strict_types=1);

namespace TextMate\Formatter;

use PhpCsFixer\Fixer\WhitespacesAwareFixerInterface;
use PhpCsFixer\FixerDefinition\CodeSample;
use PhpCsFixer\FixerDefinition\FixerDefinition;
use PhpCsFixer\FixerDefinition\FixerDefinitionInterface;
use PhpCsFixer\Tokenizer\Token;
use PhpCsFixer\Tokenizer\Tokens;

final class SwitchIndentFixer extends AbstractFormatter implements WhitespacesAwareFixerInterface {
	public function getDefinition(): FixerDefinitionInterface {
		return new FixerDefinition(
			'Removes one indent from cases inside switches.',
			[
				new CodeSample("<?php\nswitch (\$variable) {\ncase 'value':\n    break;\ndefault:\n    break;\n}\n"),
			]
		);
	}

	/**
	 * Must run after BracesFixer.
	 */
	public function getPriority(): int {
		return 35;
	}

	public function isCandidate(Tokens $tokens): bool {
		return $tokens->isTokenKindFound(\T_SWITCH);
	}

	protected function applyFix(\SplFileInfo $file, Tokens $tokens): void {
		$indent = null;
		$open = false;
		foreach ($tokens as $index => $token) {
			// Search for open switch tag.
			if (!$open) {
				if ($token->isGivenKind(\T_SWITCH)) {
					$open = true;
					$indent = \strlen($tokens[$index - 1]->getContent());
				}
				continue;
			}
			if (!$token->isGivenKind(\T_WHITESPACE)) {
				continue;
			}
			// If current whitespace length equals to closing open switch statement we found our end.
			if (isset($tokens[$index + 1]) && $tokens[$index + 1]->equals('}')) {
				if (\strlen($token->getContent()) == $indent) {
					$open = false;
					continue;
				}
			}
			// Does not start with newline.
			if ($token->getContent()[0] != "\n") {
				continue;
			}
			// Remove last char (tab) from each whitespace token that starts with a newline.
			$tokens[$index] = new Token([\T_WHITESPACE, substr($token->getContent(), 0, -1) ?: "\n"]);
		}
	}
}
