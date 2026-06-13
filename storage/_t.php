<?php
require __DIR__.'/../vendor/autoload.php';
$app = require __DIR__.'/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
app()->setLocale('es');
view()->share('errors', new \Illuminate\Support\ViewErrorBag);

use App\Models\Stay;
use App\Models\StayDocument;
use App\Models\Document;
use Barryvdh\DomPDF\Facade\Pdf;

$stay = Stay::with(['patient','room','currentDoctors.doctor.specialties'])->first();
$doc = Document::where('code','authorized_consent')->firstOrFail();
$sd = StayDocument::where('stay_id',$stay->id)->where('document_id',$doc->id)->first();

$data = $sd?->form_data ?? [
  'folio'=>'001','patient_phone'=>'417 123 4567','responsible_name'=>'MARIA LOPEZ',
  'responsible_relationship'=>'ESPOSA','responsible_address'=>'CALLE FALSA 123',
  'doctor_name'=>'DR. JESUS PARAMO','doctor_cedula'=>'1234567',
  'diagnoses'=>['APENDICITIS AGUDA','PERITONITIS'],
  'surgical_procedure'=>'APENDICECTOMIA','invasive_procedure'=>'LAPAROSCOPIA',
  'benefits'=>['RESOLUCION DEL CUADRO','MENOR DOLOR','RECUPERACION RAPIDA'],
  'risks'=>['SANGRADO','INFECCION','REACCION ANESTESICA'],
  'alternatives'=>'TRATAMIENTO CONSERVADOR',
  'designated_person'=>'MARIA LOPEZ','city'=>'ACAMBARO, GTO.',
  'signed_day'=>'13','signed_month'=>'JUNIO','signed_year'=>'2026','signed_time'=>'14:30',
  'witness_1_name'=>'PEDRO RAMIREZ','witness_2_name'=>'ANA TORRES',
];

$pdf = Pdf::loadView('pdfs.consents.authorized-consent', [
  'stay'=>$stay,'patient'=>$stay->patient,'data'=>$data,'generatedAt'=>now(),
])->setPaper('letter','portrait')->setOptions([
  'dpi'=>96,'defaultFont'=>'sans-serif','isRemoteEnabled'=>false,'isHtml5ParserEnabled'=>true,
]);
$out = $pdf->output();
file_put_contents(__DIR__.'/_authorized.pdf', $out);
echo "bytes=".strlen($out)."\n";
echo "pages=".$pdf->getDomPDF()->getCanvas()->get_page_count()."\n";
