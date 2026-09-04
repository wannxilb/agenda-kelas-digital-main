<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$cols = DB::select("select column_name from information_schema.columns where table_name='schedules' order by ordinal_position");
$colNames = array_map(function($c){ return $c->column_name; }, $cols);
echo "SCHEDULE COLS: ".implode(',', $colNames).PHP_EOL;
echo "HAS SEMESTER COL: ".(in_array('semester',$colNames)?'YA':'TIDAK').PHP_EOL;

$rows = DB::table('schedules')->where('class_id', 38)->get();
echo "JUMLAH jadwal XII RPL 1 (class 38): ".$rows->count().PHP_EOL;
$labels = DB::table('subjects')->get()->keyBy('id');
foreach($rows as $r){
  $nm = isset($labels[$r->subject_id]) ? $labels[$r->subject_id]->name : '?';
  echo $r->id.' | '.$nm.' | '.($r->day??'').' '.($r->start_time??'').'-'.($r->end_time??'').' | week='.($r->week_type??'null').' | ay='.($r->academic_year_id??'null').PHP_EOL;
}
