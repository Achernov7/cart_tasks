<html>
<head>
    <title>View</title>
</head>
<link rel="stylesheet" href="/css/common.css">
<body>
    <?php $marginLeft = 10;?>
    <div class="_component">
        <div class="_component_box">
            <div class="_component_box_menu">
            <?php foreach ($productGroups as $keyGlobal => $globalGroups) {
                // dd($productGroups);
                if ($keyGlobal != 'products') {
                    if ($keyGlobal == 'childGroups') {
                        $firstKey = array_key_first($globalGroups);
                    }
                    foreach ($globalGroups as $key => $group) {
                        $id = $group['id'];
                        $value = $group['name'];
                        $count = $group['count'];
                        $colour = $keyGlobal == 'mainGroup' ? 'blue' : 'black';
                        if ($keyGlobal == 'childGroups') {
                            if ($key == $firstKey){
                                $marginLeft += 10;
                            }
                        } else {
                            $marginLeft += 10;
                        }
                        echo 
                        "<div style='margin-left: $marginLeft'>
                        <a href='?group=$id' style='color: $colour' >$value</a> $count
                        </div>
                        ";
                    }
                }
            } ?>
            </div>
            <?php if ($keyGlobal == 'products') { ?>
            <div class="_component_box_content">
                <div class="_component_box_content_product">
                    <H1>Продукты</H1>
                    <?php
                    if (count($globalGroups) == 0) {
                        echo '<H2>Нет продуктов</H2>';
                    }?>
                    <?php foreach ($globalGroups as $product) {?>
                        <div>
                            <?php echo $product['name'];?>
                        </div>
                    <?php }?>
                </div>
            </div>
            <?php } ?>   
        </div>
    </div>
</body>
</html>