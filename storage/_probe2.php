<?php
require __DIR__.'/../vendor/autoload.php';
$app = require __DIR__.'/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
app()->setLocale('es');
view()->share('errors', new \Illuminate\Support\ViewErrorBag);
use Barryvdh\DomPDF\Facade\Pdf;
$pdf = Pdf::loadView('pdfs.consents._probe2', [])->setPaper('letter','portrait')
  ->setOptions(['dpi'=>96,'defaultFont'=>'sans-serif','isRemoteEnabled'=>false,'isHtml5ParserEnabled'=>true]);
file_put_contents(__DIR__.'/_probe2.pdf', $pdf->output());
echo "ok\n";
