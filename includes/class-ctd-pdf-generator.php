<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// Ensure FPDF is loaded before extending it
if ( ! class_exists( 'FPDF' ) ) {
    require_once plugin_dir_path( __FILE__ ) . 'lib/fpdf/fpdf.php';
}

/**
 * Extended FPDF class to support Alpha transparency and Custom Header/Footer
 */
if ( ! class_exists( 'CTD_PDF_Alpha' ) ) {
    class CTD_PDF_Alpha extends FPDF {
        protected $extgstates = array();

        // alpha: real value from 0 (transparent) to 1 (opaque)
        // bm:    blend mode, one of the following:
        //          Normal, Multiply, Screen, Overlay, Darken, Lighten, ColorDodge, ColorBurn,
        //          HardLight, SoftLight, Difference, Exclusion, Hue, Saturation, Color, Luminosity
        function SetAlpha($alpha, $bm='Normal')
        {
            // set alpha for stroking (CA) and non-stroking (ca) operations
            $gs = $this->AddExtGState(array('ca'=>$alpha, 'CA'=>$alpha, 'BM'=>'/'.$bm));
            $this->SetExtGState($gs);
        }

        function AddExtGState($parms)
        {
            $n = count($this->extgstates)+1;
            $this->extgstates[$n]['parms'] = $parms;
            return $n;
        }

        function SetExtGState($gs)
        {
            $this->_out(sprintf('/GS%d gs', $gs));
        }

        function _enddoc()
        {
            if(!empty($this->extgstates) && $this->PDFVersion<'1.4')
                $this->PDFVersion='1.4';
            parent::_enddoc();
        }

        function _putextgstates()
        {
            for ($i = 1; $i <= count($this->extgstates); $i++)
            {
                $this->_newobj();
                $this->extgstates[$i]['n'] = $this->n;
                $this->_put('<</Type /ExtGState');
                $parms = $this->extgstates[$i]['parms'];
                $this->_put(sprintf('/ca %.3F', $parms['ca']));
                $this->_put(sprintf('/CA %.3F', $parms['CA']));
                $this->_put('/BM '.$parms['BM']);
                $this->_put('>>');
                $this->_put('endobj');
            }
        }

        function _putresourcedict()
        {
            parent::_putresourcedict();
            $this->_put('/ExtGState <<');
            foreach($this->extgstates as $k=>$layer)
                $this->_put('/GS'.$k.' '.$layer['n'].' 0 R');
            $this->_put('>>');
        }

        function _putresources()
        {
            $this->_putextgstates();
            parent::_putresources();
        }
        
        // --- Custom Header ---
        function Header() {
            // 1. Watermark (Background)
            $this->Watermark();

            // 2. Logo (Left)
            // Get Site Logo
            $custom_logo_id = get_theme_mod( 'custom_logo' );
            $logo_path = '';
            if ( $custom_logo_id ) {
                $logo_path = get_attached_file( $custom_logo_id );
            }

            if ( $logo_path && file_exists( $logo_path ) ) {
                // Resize logic: maintain aspect ratio, max width 30mm (~120px @ 72dpi is approx 42mm, user asked for small 120px)
                // 120px / 96dpi * 25.4mm/in ~= 31.75mm. Let's say 32mm.
                $this->Image( $logo_path, 10, 10, 32 );
            } else {
                // Fallback text if no logo
                $this->SetFont('Arial', 'B', 12);
                $this->Cell(40, 10, get_bloginfo('name'), 0, 0, 'L');
            }

            // 3. Header Right (Contact Info)
            $this->SetXY( -80, 10 ); // Position from right
            $this->SetFont('Arial', 'B', 10);
            $this->Cell( 70, 5, 'Advago Travels Pvt. Ltd.', 0, 1, 'R' );
            
            $this->SetFont('Arial', '', 8);
            $this->SetX( -80 );
            $this->Cell( 70, 4, 'Call: +91 8745 850 015', 0, 1, 'R' );
            
            $this->SetX( -80 );
            $this->Cell( 70, 4, 'Email: info@advago.in', 0, 1, 'R' );
            
            $this->SetX( -80 );
            $this->Cell( 70, 4, 'Website: advago.in', 0, 1, 'R' );

            $this->SetX( -80 );
            // MultiCell aligns left by default, but we can align R (Right)
            // However, SetX(-80) might behave differently with MultiCell. 
            // Let's use Cell for single lines or handle MultiCell carefully.
            // Address is split into two lines manually for cleaner right alignment control
            $this->Cell( 70, 4, 'Address: C16, First floor, Main Road', 0, 1, 'R' );
            $this->SetX( -80 );
            $this->Cell( 70, 4, 'Masoodpur, Vasant Kunj, New Delhi', 0, 1, 'R' );


            // 4. Separator Line
            $this->SetY( 38 ); // Move down past header info (approx 10 + 6*4 = 34mm)
            $this->SetDrawColor(200, 200, 200);
            $this->Line( 10, $this->GetY(), 200, $this->GetY() ); // Line from X=10 to X=200
            $this->Ln(5); // Spacing after line
        }

        // --- Watermark Function ---
        function Watermark() {
            $custom_logo_id = get_theme_mod( 'custom_logo' );
            $logo_path = '';
            if ( $custom_logo_id ) {
                $logo_path = get_attached_file( $custom_logo_id );
            }

            if ( ! $logo_path || ! file_exists( $logo_path ) ) {
                return;
            }

            // Save current position
            $x = $this->GetX();
            $y = $this->GetY();

            // Set Alpha
            $this->SetAlpha(0.1); // Very light opacity

            // Pattern Logic
            // Page width ~210mm, Height ~297mm
            // Repeat every ~60mm
            $step_x = 70;
            $step_y = 80;
            $img_w = 40; // Size of watermark image

            for ( $i = 10; $i < 200; $i += $step_x ) {
                for ( $j = 40; $j < 280; $j += $step_y ) {
                    $this->Image( $logo_path, $i, $j, $img_w );
                }
            }

            // Reset Alpha
            $this->SetAlpha(1);
            
            // Restore position (though Header doesn't strictly need it as it resets automatically)
            $this->SetXY($x, $y);
        }

        // --- Footer ---
        function Footer() {
            // Position at 1.5 cm from bottom
            $this->SetY(-15);
            $this->SetFont('Arial', 'I', 8);
            $this->SetTextColor(128);
            // Page number
            $this->Cell(0, 10, 'Page ' . $this->PageNo() . '/{nb}', 0, 0, 'C');
        }
    }
}

class CTD_PDF_Generator {

    public function __construct() {
        add_action( 'init', array( $this, 'handle_pdf_download' ) );
    }

    public function handle_pdf_download() {
        if ( isset( $_GET['ctd_action'] ) && $_GET['ctd_action'] === 'download_itinerary' && isset( $_GET['post_id'] ) ) {
            $post_id = intval( $_GET['post_id'] );
            
            // Check if PDF download is enabled for this post
            $enabled = get_post_meta( $post_id, 'ctd_enable_itinerary_pdf', true );
            if ( ! $enabled ) {
                wp_die( 'Itinerary download is disabled for this trip.' );
            }

            $this->generate_pdf( $post_id );
            exit;
        }
    }

    private function generate_pdf( $post_id ) {
        // Instantiate custom PDF class
        $pdf = new CTD_PDF_Alpha();
        $pdf->AliasNbPages(); // For total page count
        $pdf->AddPage();
        
        // --- Trip Title & Details ---
        $pdf->SetFont('Arial', 'B', 20);
        $title = get_the_title( $post_id );
        $title = $this->clean_text( $title );
        $pdf->MultiCell(0, 10, $title, 0, 'L');
        $pdf->Ln(2);

        // Price & Duration
        $duration = get_post_meta( $post_id, 'ctd_duration_days', true );
        $min_price = get_post_meta( $post_id, 'ctd_min_price', true );
        
        // Format Price
        $price_text = '';
        if ( $min_price ) {
             $price_text = 'From: ' . $this->format_inr( $min_price );
        } else {
             // Try to get from packages if min_price meta is empty
             $packages = get_post_meta( $post_id, 'ctd_pricing_packages', true );
             if ( ! empty( $packages ) && is_array( $packages ) ) {
                 foreach ( $packages as $pkg ) {
                     if ( ! empty( $pkg['prices'] ) ) {
                         foreach ( $pkg['prices'] as $price_data ) {
                             if ( ! empty( $price_data['regular_price'] ) ) {
                                 $price_text = 'From: ' . $this->format_inr( $price_data['regular_price'] );
                                 break 2;
                             }
                         }
                     }
                 }
             }
        }

        $pdf->SetFont('Arial', 'B', 12);
        $pdf->SetTextColor(100, 100, 100); // Dark Gray
        
        $details_line = '';
        if ( $duration ) {
            $details_line .= $duration . ' Days';
        }
        if ( $duration && $price_text ) {
            $details_line .= '  |  ';
        }
        if ( $price_text ) {
            $details_line .= $price_text;
        }
        
        if ( $details_line ) {
            $pdf->Cell(0, 10, $this->clean_text( $details_line ), 0, 1, 'L');
        }
        
        $pdf->Ln(5);
        $pdf->SetTextColor(0, 0, 0); // Reset Black

        // --- Itinerary ---
        $itineraries = get_post_meta( $post_id, 'ctd_itineraries', true );
        
        if ( ! empty( $itineraries ) && is_array( $itineraries ) ) {
            // Section Header
            $pdf->SetFont('Arial', 'B', 16);
            $pdf->SetFillColor(240, 240, 240); // Light Gray Background
            $pdf->Cell(0, 12, '  Itinerary', 0, 1, 'L', true);
            $pdf->Ln(5);

            foreach ( $itineraries as $index => $item ) {
                $day = $index + 1;
                $day_title = isset( $item['title'] ) ? $item['title'] : '';
                $content = isset( $item['content'] ) ? $item['content'] : '';
                $image_id = isset( $item['image_id'] ) ? $item['image_id'] : '';

                // Clean Data
                $day_title = $this->clean_text( $day_title );
                
                // Remove Gutenberg comments and tags
                $content = preg_replace('/<!--\s*\/?wp:.*?-->/', '', $content);
                $content = strip_tags( $content ); 
                $content = $this->clean_text( $content );

                // Calculate required height for this day's section
                // Title Height (approx 10mm) + Separator (8mm) + Max(Image, Content) + Separator (8mm)
                
                // Estimate content height
                $pdf->SetFont('Arial', '', 11);
                // Content width will be ~140mm (190 - 40 - 10)
                $est_content_lines = ceil( $pdf->GetStringWidth( $content ) / 140 ); 
                $est_content_h = $est_content_lines * 6; // 6mm per line
                
                // Image Height (Max 40mm width -> assume square or 4:3, max height ~40mm)
                $est_img_h = 40; 
                
                $required_h = 10 + 8 + max($est_content_h, $est_img_h) + 8;
                
                // Footer Height ~15mm (Page Number only) + Bottom Margin 10mm = 25mm safe zone
                // Page Height 297mm
                // If current Y + required H > 270 (approx), add page
                if ( $pdf->GetY() + $required_h > 270 ) {
                    $pdf->AddPage();
                }

                // Day Title
                $pdf->SetFont('Arial', 'B', 15); // Increased to 20px (approx 15pt)
                $pdf->SetTextColor(0, 51, 102); // Dark Blue for Day Title
                
                // Use MultiCell for Title to allow wrapping
                $pdf->MultiCell(0, 8, "Day $day: $day_title", 0, 'L');
                
                $pdf->SetTextColor(0, 0, 0); // Reset
                
                // --- Side-by-Side Layout ---
                $y_start = $pdf->GetY();
                $img_w = 40; // Image Width
                $gap = 10;
                $content_start_x = 10; // Default if no image
                $content_w = 190; // Default full width
                $y_end_img = $y_start;

                // Image Logic
                if ( $image_id ) {
                    $img_path = get_attached_file( $image_id );
                    if ( $img_path && file_exists( $img_path ) ) {
                        // Place Image at X=10
                        // Calculate height proportional to width 40mm
                        list($orig_w, $orig_h) = getimagesize($img_path);
                        if ( $orig_w > 0 ) {
                            $calc_h = ($orig_h / $orig_w) * $img_w;
                            
                            // Draw Rounded Rect Clipping Area
                            // FPDF doesn't natively support rounded image clipping easily without extensions.
                            // For simplicity, we'll draw the image standard. 
                            // Or we can simulate a rounded border over it, but that corners the image.
                            // Let's stick to standard image for stability, but we can add a border.
                            $pdf->Image( $img_path, 10, $y_start, $img_w );
                            
                            // Rounded corner simulation is complex in pure FPDF without extensions (ClippingPath).
                            // We will skip actual rounded clipping to avoid breaking the PDF generation.
                            
                            $y_end_img = $y_start + $calc_h;
                            
                            // Adjust Content position
                            $content_start_x = 10 + $img_w + $gap;
                            $content_w = 190 - $img_w - $gap;
                        }
                    }
                }

                // Content
                $pdf->SetXY($content_start_x, $y_start);
                $pdf->SetFont('Arial', '', 11); // 15px is approx 11pt
                $pdf->MultiCell($content_w, 6, $content);
                $y_end_text = $pdf->GetY();
                
                // Move to below the lowest element
                $pdf->SetY( max($y_end_img, $y_end_text) );
                
                // Separator between days
                $pdf->Ln(8);
                $pdf->SetDrawColor(220, 220, 220);
                $pdf->Line( 10, $pdf->GetY(), 200, $pdf->GetY() );
                $pdf->Ln(8);
            }
        }
        
        // Clean filename
        $filename = sanitize_title( get_the_title( $post_id ) ) . '-itinerary.pdf';

        $pdf->Output('D', $filename);
    }

    private function clean_text( $text ) {
        // Decode HTML entities
        $text = html_entity_decode( $text, ENT_QUOTES, 'UTF-8' );
        // Convert to ISO-8859-1 for FPDF (supports Western European languages)
        // Using //TRANSLIT to approximate characters that can't be represented
        if ( function_exists( 'iconv' ) ) {
            return iconv( 'UTF-8', 'ISO-8859-1//TRANSLIT', $text );
        }
        return $text;
    }

    private function format_inr( $amount ) {
        $amount = (string) $amount;
        $parts = explode( '.', $amount );
        $int = $parts[0];
        $dec = isset( $parts[1] ) ? '.' . $parts[1] : '';
        $last3 = substr( $int, -3 );
        $rest = substr( $int, 0, -3 );
        if ( $rest !== '' ) {
            $rest = preg_replace( '/\B(?=(\d{2})+(?!\d))/', ',', $rest );
        }
        return ( $rest ? $rest . ',' : '' ) . $last3 . $dec;
    }
}

new CTD_PDF_Generator();
