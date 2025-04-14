<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Barcode</title>
    <style>
        @page {
            margin: 0;
            padding: 0;
        }
        html, body {
            margin: 0;
            padding: 0;
            font-family: Arial, sans-serif;
            text-align: center;
        }
        .container {
            padding: 0;
            margin: 0;
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            width: 100%;
            height: 100%;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
        }
        .content-wrapper {
            width: 95%;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: space-between;
            height: 100%;
            padding: 1% 0;
        }
        .team-name {
            font-weight: bold;
            font-size: {{ min(12, max(8, $paper_height * 0.05)) }}pt; /* Increased font size */
            line-height: 1;
            width: 100%;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
            margin: 0;
            padding-top: 1mm; /* Added top padding */
        }
        .product-name {
            font-size: {{ min(10, max(8, $paper_height * 0.04)) }}pt; /* Increased font size */
            line-height: 1;
            width: 100%;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
            margin: 0;
        }
        .barcode-container {
            width: 75%;
            margin: 0 auto;
            display: flex;
            justify-content: center;
            align-items: center;
            flex-grow: 1;
        }
        .barcode-container img {
            max-width: 100%;
            max-height: {{ $paper_height * 0.40 }}mm; /* Kept at 55% to leave room for text */
            width: auto;
            height: auto;
        }
        .barcode-text {
            font-size: {{ min(10, max(7, $paper_height * 0.045)) }}pt; /* Increased font size */
            line-height: 1.1;
            width: 100%;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
            margin: 0;
        }
        .price {
            font-weight: bold;
            font-size: {{ min(14, max(8, $paper_height * 0.055)) }}pt; /* Increased font size */
            line-height: 1.1;
            width: 100%;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
            margin: 0;
        }
        /* Count visible elements to adjust spacing */
        .spacer {
            flex-grow: 0.1;
            height: {{ $paper_height * 0.02 }}mm; /* Increased spacer height */
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="content-wrapper">
            @if($show_team_name && !empty($team_name))
                <div class="team-name">{{ $team_name }}</div>
                <div class="spacer"></div>
            @endif
            
            @if($show_product_name && !empty($product_name))
                <div class="product-name">{{ $product_name }}</div>
                <div class="spacer"></div>
            @endif
            
            <div class="barcode-container">
                <img src="data:image/png;base64,{{ $barcode_image }}" alt="Barcode">
            </div>
            
            @if($show_content)
                <div class="spacer"></div>
                <div class="barcode-text">{{ $barcode_content }}</div>
            @endif
            
            @if($show_price && !empty($price))
                <div class="spacer"></div>
                <div class="price">{{ $price }}</div>
            @endif
        </div>
    </div>
</body>
</html>
