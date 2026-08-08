<?php $badgeClass = strtolower(str_replace(' ', '-', (string) ($badgeStatus ?? ''))); ?><span class="badge badge-<?= e($badgeClass) ?>"><?= e($badgeStatus ?? '') ?></span>
