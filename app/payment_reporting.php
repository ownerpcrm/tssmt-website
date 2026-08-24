<?php declare(strict_types=1);

/** Shared reporting ranges for admin payment and donation lists. */
function payment_date_range(?string $requested): array {
    $key=$requested??'this_month';
    $today=new DateTimeImmutable('today');
    $ranges=[
        'today'=>['Today',$today],
        'this_week'=>['This week',$today->modify('monday this week')],
        'this_month'=>['This month',$today->modify('first day of this month')],
        'this_quarter'=>['This quarter',$today->setDate((int)$today->format('Y'),((int)(floor(((int)$today->format('n')-1)/3)*3)+1),1)],
        'this_year'=>['This year',$today->setDate((int)$today->format('Y'),1,1)],
        'last_30'=>['Last 30 days',$today->modify('-29 days')],
        'last_90'=>['Last 90 days',$today->modify('-89 days')],
        'last_180'=>['Last 180 days',$today->modify('-179 days')],
        'last_year'=>['Last 365/366 days',$today->modify('-1 year')->modify('+1 day')],
        'all'=>['All time',null],
    ];
    if(!isset($ranges[$key]))$key='this_month';
    return ['key'=>$key,'label'=>$ranges[$key][0],'start'=>$ranges[$key][1]?->format('Y-m-d'),'options'=>array_map(static fn(array $range):string=>$range[0],$ranges)];
}

function payment_range_query(PDO $pdo,string $sql,array $range,string $orderBy): array {
    if($range['start']===null)return $pdo->query($sql.' '.$orderBy)->fetchAll();
    $statement=$pdo->prepare($sql.' WHERE payment_date >= ? '.$orderBy);
    $statement->execute([$range['start']]);
    return $statement->fetchAll();
}

function payment_range_filter(array $range): void { ?>
<style>.admin-mode main{position:relative}.admin-mode .reporting-filter{position:absolute;top:28px;right:34px;margin:0}@media(max-width:800px){.admin-mode .reporting-filter{right:20px}}@media(max-width:700px){.admin-mode .reporting-filter{position:static;margin:0 0 20px}}</style>
<form method="get" class="reporting-filter">
  <label>Date range
    <select name="range" onchange="this.form.submit()">
      <?php foreach($range['options'] as $key=>$label): ?><option value="<?=e($key)?>" <?=$range['key']===$key?'selected':''?>><?=e($label)?></option><?php endforeach; ?>
    </select>
  </label>
  <noscript><button type="submit">Apply</button></noscript>
  <a class="button" href="?range=<?=rawurlencode($range['key'])?>&export=excel">Export to Excel</a>
</form>
<?php }

function export_excel_xml(string $filename,array $headings,array $rows): never {
    header('Content-Type: application/vnd.ms-excel; charset=UTF-8');
    header('Content-Disposition: attachment; filename="'.$filename.'.xls"');
    header('Cache-Control: max-age=0');
    $cell=static fn($value):string=>htmlspecialchars((string)$value,ENT_XML1|ENT_QUOTES,'UTF-8');
    echo "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n";
    echo '<Workbook xmlns="urn:schemas-microsoft-com:office:spreadsheet" xmlns:ss="urn:schemas-microsoft-com:office:spreadsheet"><Worksheet ss:Name="Report"><Table>';
    echo '<Row>'; foreach($headings as $heading)echo '<Cell><Data ss:Type="String">'.$cell($heading).'</Data></Cell>'; echo '</Row>';
    foreach($rows as $row){echo '<Row>';foreach($row as $value)echo '<Cell><Data ss:Type="String">'.$cell($value).'</Data></Cell>';echo '</Row>';}
    echo '</Table></Worksheet></Workbook>';
    exit;
}
