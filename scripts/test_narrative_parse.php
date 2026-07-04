<?php

$root = dirname(__DIR__);
require $root . '/vendor/autoload.php';
$app = require_once $root . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$narrative = <<<'TEXT'
Summary
: The candidate has a strong background in data governance and business analysis.

Strengths
:
Proven track record in financial and insurance sector.
Skilled at data analysis, governance frameworks, and implementing robust data solutions.
Experienced in leading and mentoring teams onshore and offshore.

Gaps
No ServiceNow experience listed.
TEXT;

$service = app(App\Services\Resume\ResumeAnalysisService::class);
$ref = new ReflectionClass($service);
$method = $ref->getMethod('parseNarrativeSections');
$method->setAccessible(true);
$result = $method->invoke($service, $narrative);

echo "Summary: {$result['summary']}\n\n";
echo "Strengths (" . count($result['strengths']) . "):\n";
foreach ($result['strengths'] as $i => $s) {
    echo ($i + 1) . '. ' . $s . "\n";
}
