<!DOCTYPE html>
<html>
    <head>
        <meta charset="UTF-8">
        <title>Archivos enviados</title>
    </head>
    <style>
        body, html, root, p, table {
            margin:0; 
            padding:0;            
        }
        body { 
            margin:0; 
            padding:0; 
            font-size: 11px; 
            font-family: sans-serif; 
        } 
        table { 
            border-collapse: collapse; 
        }
        table td, table th { 
            font-size: 11px; 
            font-family: sans-serif; 
            border: 1px #555 solid; 
        }
    </style>
    <body>
        <p>RFC: <?= $this->data["rfc"] ?></p>
        <table>
            <thead>
                <tr>
                    <th>Patente</th>
                    <th>Aduana</th>
                    <th>Pedimento</th>
                    <th>M3</th>
                    <th>VAL</th>
                    <th>PAG</th>
                    <th>PAG_E</th>
                </tr>
            </thead>
            <tbody>
                <?php if (isset($this->arr) && !empty($this->arr)) { ?>
                    <?php foreach ($this->arr as $item) { ?>
                        <tr>
                            <td><?= $item["patente"] ?></td>
                            <td><?= $item["aduana"] ?></td>
                            <td><?= $item["pedimento"] ?></td>
                            <td><?= $item["m3"]["archivoNombre"] ?></td>
                            <td><?= $item["firma"]["archivoNombre"] ?></td>
                            <td><?= $item["pago"]["archivoNombre"] ?></td>
                            <td><?= $item["pagoe"]["archivoNombre"] ?></td>
                        </tr>
                    <?php } ?>
                <?php } else { ?>
                    <tr>
                        <td colspan="7">No hay archivos.</td>
                    </tr>
                <?php } ?>
            </tbody>
        </table>
    </body>
</html>