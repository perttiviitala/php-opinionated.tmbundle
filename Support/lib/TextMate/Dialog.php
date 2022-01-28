<?php

declare(strict_types=1);

namespace TextMate;

class Dialog {
	private string $path;

	public function __construct(string $path) {
		$this->path = $path;
	}

	public function tooltip(string ...$lines): void {
		$html = sprintf(
			<<<'HTML'
			<style>
				body {
					position: absolute;
					font-size: 13px;
					font-weight: 500;
					font-family: "Fira Code", "Menlo";
				}
				div {
					animation: fadeIn linear 0.15s;
					padding: 8px;
					color: #eee;
					background-color: #202020;
					border: 1px outset #000;
					border-radius: 8px;
				}
				p {
					white-space: pre-wrap;
					margin: 0.5em 0 0 0;
				}
				.lines p:last-child {
					padding-bottom: 0.5em;
				}

				@keyframes fadeIn {
					from { opacity: 0; margin-top: 50px; }
				}

				/* Used by function tooltips */
				span.initializer  { color: #CE8462; }
				span.keyword      { color: #D2B780; }
				span.classname    { color: #A999AC; }
				span.methodname   { color: #9F7F51; }
				span.methodparam  { color: #6E798D; }
				span.modifier     { color: #F4EDAB; }
				span.replaceable  { color: #808080; }
				span.type         { color: #F4EDAB; }
				span.type.false   { color: #CE8462; }
			</style>
			<div class="lines">
				%s
			</div>
			HTML,
			implode("\n\t", array_map(fn ($line) => "<p>{$line}</p>", $lines)),
		);

		shell_exec(sprintf(
			'%s tooltip --transparent --html %s',
			escapeshellarg($this->path),
			escapeshellarg($html),
		));
	}

	public function completions(string $typed, string ...$found): void {
		$suggestions = [];
		foreach ($found as $signature) {
			$display = strip_tags($signature);

			preg_match_all('/\$(\w+)/', $display, $matches);
			$counter = 1;
			$params = array_map(
				function ($param) use (&$counter) {
					return sprintf('${%d:\\\\$${%d:%s}}', $counter++, $counter++, $param);
				},
				$matches[1],
			);

			$display = str_replace('"', '\"', $display);

			$suggestions[] = [
				'image' => 'Snippet',
				// Using image pushes content out of box without whitespace padding.
				'display' => $display.'       ',
				'match' => strtok($display, ' '),
				'insert' => sprintf('(%s)', implode(', ', $params)),
			];
		}

		shell_exec(sprintf(
			<<<TEXT
			%s popup \
				--alreadyTyped %s \
				--additionalWordCharacters %s \
				--suggestions %s \
				--caseInsensitive
			TEXT,
			escapeshellarg($this->path),
			escapeshellarg($typed),
			escapeshellarg('_'),
			escapeshellarg((new Plist())->arrayToPlist($suggestions)),
		));
	}
}
