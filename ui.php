<?php
require_once __DIR__ . '/helpers.php';

function render_page_start(string $title, string $subtitle = '', array $links = []): void
{
    echo '<!doctype html><html lang="en"><head><meta charset="utf-8">';
    echo '<meta name="viewport" content="width=device-width, initial-scale=1">';
    echo '<title>' . h($title) . '</title>';
    echo '<link rel="stylesheet" href="' . h(app_path('/assets/backend.css')) . '">';
    echo '</head><body>';
    echo '<div class="topbar"><div class="topbar-inner"><div class="topbar-title">Insurance Finance Portal</div><div class="actions">';
    foreach ($links as $link) {
        echo '<a class="btn secondary" href="' . h(app_path($link['href'])) . '">' . h($link['label']) . '</a>';
    }
    echo '</div></div></div>';
    echo '<div class="container"><div class="card">';
    echo '<h2>' . h($title) . '</h2>';
    if ($subtitle !== '') {
        echo '<p class="subtitle">' . h($subtitle) . '</p>';
    }
}

function render_page_end(): void
{
    echo '</div></div></body></html>';
}
