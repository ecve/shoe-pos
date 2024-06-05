<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Document</title>
    <style>
        #code {
            display: none
        }
        /* @media print {


        } */
    </style>

</head>

<body>
    @php
        $banner_Information = \App\Models\BannerInformation::first();
        use Picqer\Barcode\BarcodeGeneratorPNG;

    @endphp
    <div>
        {{-- <p>
            {!! DNS1D::getBarcodeSVG($getPurchaseBarcode->barcode, 'C39', .8, 40) !!}

        </p> --}}



        <p style="text-align: center;" style="font-size: 14px;">
            {{-- <img style="width: 20%" src="{{ $banner_Information->banner_logo }}" alt="logo" /><br> --}}
            <span style="font-weight: bold;font-size:28px;font-family:Cambria, Cochin, Georgia, Times, 'Times New Roman', serif">J & B ENTERPRISE</span><br />
            {{-- <span style="font-weight: bold;font-size:25px">Batch:{{ $getPurchaseBarcode->batch }}</span><br> --}}

            <p  style="padding-left: 30px;text-align:center; font-weight: bold;font-size:42px; letter-spacing:.15rm; font-family:'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; margin:0">{{ $getPurchaseBarcode->barcode }}</p><br />

            {{-- {!! barcode::getBarcode($getPurchaseBarcode->barcode, $barcodeGenerator::TYPE_CODE_128)!!} --}}

            @php
                $barcodeGenerator = new BarcodeGeneratorPNG();
                $barcode = $barcodeGenerator->getBarcode($getPurchaseBarcode->barcode, $barcodeGenerator::TYPE_CODE_128);
            @endphp


            <div class="barcode-image" style="display: flex; align-items:center; justify-content:center;margin:0;padding-left:2px">
                <img style="display:block;margin-left: 20px; width:400px; height:38px"  src="data:image/png;base64,{{ base64_encode($barcode) }}" alt="Generated Barcode"><br/>
            </div>

            <p style="text-align:center;font-weight: bold;font-size:38px;margin:0;font-family:Cambria, Cochin, Georgia, Times, 'Times New Roman', serif">Price: {{ $getPurchaseBarcode->sales_price }}</p>
        </p>
    </div>
    <script>
        window.print();
    </script>
</body>

</html>
