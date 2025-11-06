<style>
    table {
        font-family: "Open Sans", sans-serif;
        font-size: 12px;
    }
</style>

<h2>Laporan Produksi Picker</h2>

<table class="table table-striped table-bordered">
    <tbody>
    <tr>
        <td>Periode</td>
        <td>:</td>
        <td><?= explode(" - ", $reportrange)[0] ?></td>
    </tr>
    <tr>
        <td>Sampai dengan</td>
        <td>:</td>
        <td><?= explode(" - ", $reportrange)[1] ?></td>
    </tr>
    <tr>
        <td>Total data</td>
        <td>:</td>
        <td><?= $grand_total ?></td>
    </tr>
    </tbody>
</table>

<br>

<table class="table table-striped table-bordered">
    <thead>
    <tr>
        <th>No.</th>
        <th>Nama</th>
        <th>Tanggal</th>
        <th>Jumlah</th>
    </tr>
    </thead>
    <tbody>
    <?php $i = 1;
    foreach ($list_data as $key => $value) : ?>
        <tr>
            <td><?= $i++ ?></td>
            <td colspan="2"><?= $key ?></td>
        </tr>
        <?php $sub_total = 0; ?>
        <?php foreach ($value as $item): ?>
            <?php $sub_total += $item['total'] ?>
            <tr>
                <td></td>
                <td></td>
                <td><?= empty($item['tanggal']) ? null : date('Y-m-d H:i:s', strtotime($item['tanggal'])) ?></td>
                <td align="right"><?= $item['total'] ?></td>
            </tr>
            <?php if (isset($item['status_performa']) && !empty($item['status_performa'])): ?>
            <tr>
                <td></td>
                <td></td>
                <td style="padding-left: 20px; font-style: italic; color: #666;"><?= $item['status_performa'] ?></td>
                <td></td>
            </tr>
            <?php endif; ?>
        <?php endforeach; ?>
        <tr>
            <td></td>
            <td></td>
            <td align="right">Total</td>
            <td align="right"><?= $sub_total ?></td>
        </tr>
    <?php endforeach; ?>
    </tbody>
</table>