<?php

namespace App\Http\Controllers;

use App\Barcode;
use App\Contact;
use App\Product;
use App\Utils\ProductUtil;
use Illuminate\Http\Request;
use App\Utils\TransactionUtil;
use App\Custom\CustomeTransactionUtil;

class LabelsController extends Controller
{
    /**
     * All Utils instance.
     *
     */
    protected $transactionUtil;
    protected $productUtil;
    protected $CustometransactionUtil;

    /**
     * Constructor
     *
     * @param TransactionUtil $TransactionUtil
     * @return void
     */
    public function __construct(TransactionUtil $transactionUtil, ProductUtil $productUtil, CustomeTransactionUtil $CustometransactionUtil )
    {
        $this->transactionUtil = $transactionUtil;
        $this->productUtil = $productUtil;
        $this->CustometransactionUtil = $CustometransactionUtil;
    }

    /**
     * Display labels
     *
     * @return \Illuminate\Http\Response
     */
    public function show(Request $request)
    {
        $business_id = $request->session()->get('user.business_id');
        $purchase_id = $request->get('purchase_id', false);
        $product_id = $request->get('product_id', false);
        $contact_id = $request->get('contact_id', false);
        $supplier = null;
        $transaction_date = null;
        //Get products for the business
        $products = [];
        if ($purchase_id) {
            $products = $this->transactionUtil->getPurchaseProducts($business_id, $purchase_id);
            
            $supplier = $this->CustometransactionUtil->getPurchaseSupplier($business_id, $purchase_id);
            
            $transaction_date=date_create($supplier->transaction_date);
            //echo date_format($date,"Y/m/d H:i:s");
            $transaction_date = date_format($transaction_date,"dmy");
            //return $transaction_date;

        } elseif ($product_id) {
            $products = $this->productUtil->getDetailsFromProduct($business_id, $product_id);
        }elseif($contact_id){
            $supplier = Contact::where('contact_id',$contact_id)->select('supplier_business_name','name','contact_id')->first();
            $supplier->ref_no = ''; 
            // $supplier->transaction_date ='';
        }

        $barcode_settings = Barcode::where('business_id', $business_id)
                                ->orWhereNull('business_id')
                                ->pluck('name', 'id');

        return view('labels.show')
            ->with(compact('products', 'barcode_settings','supplier','transaction_date'));
    }

    /**
     * Returns the html for product row
     *
     * @return \Illuminate\Http\Response
     */
    public function addProductRow(Request $request)
    {
        if ($request->ajax()) {
            $product_id = $request->input('product_id');
            $variation_id = $request->input('variation_id');
            $business_id = $request->session()->get('user.business_id');
            
            if (!empty($product_id)) {
                $index = $request->input('row_count');
                $products = $this->productUtil->getDetailsFromProduct($business_id, $product_id, $variation_id);
                
                return view('labels.partials.show_table_rows')
                        ->with(compact('products', 'index'));
            }
        }
    }

    /**
     * Returns the html for labels preview
     *
     * @return \Illuminate\Http\Response
     */
    public function preview(Request $request)
    {
        try {
            $products = $request->get('products');
            $print = $request->get('print');
            $barcode_setting = $request->get('barcode_setting');
            $business_id = $request->session()->get('user.business_id');
            $contact_id = $request->get('contact_id');

            $barcode_details = Barcode::find($barcode_setting);
            //print_r($barcode_details);exit;
            $business_name = $request->session()->get('business.name');

            $product_details = [];
            $total_qty = 0;
            foreach ($products as $value) {
                $details = $this->productUtil->getDetailsFromVariation($value['variation_id'], $business_id, null, false);
                $product_details[] = ['details' => $details, 'qty' => $value['quantity']];
                $total_qty += $value['quantity'];
            }

            $page_height = null;
            if ($barcode_details->is_continuous) {
                $rows = ceil($total_qty/$barcode_details->stickers_in_one_row) + 0.4;
                $barcode_details->paper_height = $barcode_details->top_margin + ($rows*$barcode_details->height) + ($rows*$barcode_details->row_distance);
            }

            $output = view('labels.partials.preview')
                ->with(compact('print', 'product_details', 'business_name', 'barcode_details', 'page_height','contact_id'))->render();

            // $output = ['html' => $html,
            //                 'success' => true,
            //                 'msg' => ''
            //             ];
        } catch (\Exception $e) {
            \Log::emergency("File:" . $e->getFile(). "Line:" . $e->getLine(). "Message:" . $e->getMessage());

            $output = __('lang_v1.barcode_label_error');
        }

        return $output;
    }
}
