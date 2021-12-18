<?php

declare(strict_types=1);

namespace TextMate;

class Dialog {
	private string $path;

	public function __construct(string $path) {
		$this->path = $path;
	}

	public function tooltip(string $title, array $lines): void {
		$this->rawHtml(sprintf(
			<<<HTML
			<style>
				body {
					position: absolute;
					top: -40px;
					left: -40px;
					font-size: 12px;
					font-family: "Fira Code", "Menlo";
					background-color: transparent;
				}
				div {
					animation: fadeIn linear 0.15s;
					padding: 8px;
					margin: 40px 40px;
					color: #eee;
					background-color: #000;
					box-shadow: 0 11px 35px 2px rgba(0, 0, 0, 0.56);
					border: 1px outset #000;
					border-radius: 8px;
				}
				p {
					white-space: pre-wrap;
					margin: 0.5em 0 0 0;
				}
				.lines p:first-child {
					font-size: 1.1em;
					font-weight: bold;
				}
				.lines p:nth-child(2) {
					padding-top: 0.5em;
					border-top: 1px solid #444;
				}
				.lines p:last-child {
					padding-bottom: 0.5em;
				}

				@keyframes fadeIn {
					0%% {
						padding-top: 2em;
						opacity: 0;
					}
					100%% {
						opacity: 1;
					}
				}
			</style>
			<div class="lines">
				<p>%s</p>%s
			</div>
			HTML,
			$title,
			implode('', array_map(fn ($line) => "<p>{$line}</p>", $lines)),
		));
	}

	public function rawHtml(string $html): void {
		shell_exec(sprintf(
			'%s tooltip --html %s',
			escapeshellarg($this->path),
			escapeshellarg($html),
		));
	}
}
