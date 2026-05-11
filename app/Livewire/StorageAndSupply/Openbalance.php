<?php

namespace App\Livewire\StorageAndSupply;

use App\Models\{GRNOpenBalance, Item, OpenBalanceItem, StockLedgerControl, Subdepartment};
use Illuminate\Support\{Arr, Facades\DB};
use Livewire\{Component, WithFileUploads};
use PhpOffice\PhpSpreadsheet\{Reader\Html, Style\Border, Style\Color, Writer\Xlsx};
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use stdClass;

class Openbalance extends Component
{
    use WithFileUploads;

    public $items, $sel_item, $selected_items = [], $stores, $open_balance;
    public $cons_type = '%%', $item_name = '', $items_file;

    public function render()
    {
        return view('livewire.storage-and-supply.openbalance');
    }

    public function mount()
    {
        // Check if editing an existing open balance
        $editId = request()->query('edit');
        if ($editId) {
            $this->open_balance = GRNOpenBalance::with('items')->find($editId);
            if (!$this->open_balance) {
                $this->dispatch('error', 'Open balance not found.');
                $this->open_balance = new GRNOpenBalance(['date' => date('Y-m-d')]);
            } elseif ($this->open_balance->status == 'approved') {
                $this->dispatch('warning', 'This open balance is already approved and cannot be edited.');
            }
        } else {
            $this->open_balance = new GRNOpenBalance(['date' => date('Y-m-d')]);
        }

        $this->sel_item = new Item();
        $this->stores = stores();
        // $this->loadItems();
        $this->dispatch('modal-show', 'open-balance');
    }

    public function store($save = false)
    {
        $this->validate($this->toValidate());
        if ($save) {
            $this->open_balance->status = 'active';
        }
        if (!$this->open_balance->save()) {
            return $this->dispatch('error', 'Error, failed to add open balance. Refresh and try again.');
        }
        $this->dispatch('success', 'Successful added open balance.');
    }

    public function approve()
    {
        DB::beginTransaction();
        try {
            // Change status in open balance
            // add approved datetime
            $this->open_balance->status = 'approved';
            $this->open_balance->approved_at = now();
            $this->open_balance->save();
            // add to stock ledger
            $items = [];
            foreach ($this->open_balance->items as $itm) {
                $pre_balance = itemBalance($this->open_balance->subdepartment_id, $itm->item_id) ?? 0;
                $items[] = [
                    'created_by' => auth()->id(),
                    'item_id' => $itm->item_id,
                    'subdepartment_id' => $this->open_balance->subdepartment_id,
                    // 'internal_source' => 1,
                    // 'external_source' => 1,
                    // 'patient_id' => 1,
                    'document_number' => $this->open_balance->id,
                    'pre_balance' => $pre_balance,
                    'post_balance' => $itm->quantity + $pre_balance,
                    'movement_type' => 'Open Balance',
                    'movement_date' => now(),
                    'created_at' => now(),
                ];
            }
            StockLedgerControl::insert($items);
            DB::commit();
            $this->open_balance = new GRNOpenBalance(['date' => date('Y-m-d')]);
            $this->dispatch('success', "Successful approved and re-adjusted the stock!");
        } catch (\Throwable $th) {
            DB::rollBack();
            $this->dispatch('error', "Failed approve. Refresh and try again.");
        }
    }

    public function loadItem($id)
    {
        if (!$store = $this->open_balance->subdepartment_id) {
            $this->dispatch('error', 'Error, please select a store first.');
        }
        if (!$item = Item::find($id)) {
            return $this->dispatch('error', 'Item not found. Refresh and try again.');
        }
        $balance = itemBalance($store, $item->id);
        if ($balance && $balance > 0) {
            // return $this->dispatch('error', 'Failed, open balance was already set. Choose another item.');
        }
        $item->balance = $balance; //0;
        $this->sel_item = $item;
    }

    public function setQuantity()
    {
        $item = $this->sel_item;
        if (!$item->units || !is_numeric($item->units)) {
            $this->sel_item->units = 1;
        }
        if (!$item->per_unit || !is_numeric($item->per_unit)) {
            $this->sel_item->per_unit = 1;
        }
        if (
            $this->sel_item->per_unit == 1
            && $this->sel_item->units == 1
            && is_numeric($item->quantity)
        ) {
            $this->sel_item->per_unit = $item->quantity;
        }
        $this->sel_item->quantity = $this->sel_item->per_unit * $this->sel_item->units;
    }

    public function addItem()
    {
        $this->validate();
        $item = $this->sel_item;
        if (!isset($item->id)) {
            return $this->dispatch('error', 'Please select an item first to add.');
        }

        DB::beginTransaction();
        try {
            if (!isset($this->open_balance->id)) {
                $this->store();
            }
            $this->open_balance->items()->create([
                'item_id' => $item->id,
                'units' => $item->units,
                'items_per_unit' => $item->per_unit,
                'buying_price' => $item->buying_price,
                'batch_no' => $item->batch_no,
                'expiry_date' => $item->expiry_date,
            ]);
            DB::commit();
            $this->sel_item = new Item();
            $this->dispatch('refresh');
            $this->dispatch('success', "Successful added item!");
        } catch (\Throwable $th) {
            DB::rollBack();
            $this->dispatch('error', "Failed to add item. Refresh and try again.");
        }
    }

    public function loadSelectedItems()
    {
        $items = [];
        foreach ($this->open_balance->items as $itm) {
            $sel = new stdClass;
            $sel->id = $itm->id;
            $sel->item_id = $itm->item_id;
            $sel->name = $itm->item->name;
            $sel->unit = $itm->item->unit;
            $sel->units = $itm->units;
            $sel->per_unit = $itm->items_per_unit;
            $sel->buying_price = $itm->buying_price;
            $sel->batch_no = $itm->batch_no;
            // Use accessor which handles virtual column
            $sel->quantity = $itm->quantity;
            $sel->expiry_date = $itm->expiry_date;
            $items[] = $sel;
        }
        return $items;
    }

    public function storeChanged()
    {
        if ($grn = GRNOpenBalance::whereStatus('pending')->where('created_by', auth()->id())->first()) {
            return $this->open_balance = $grn;
        }
        $this->reset('sel_item');
        $this->open_balance = $this->open_balance;
    }

    public function deleteItem($id)
    {
        if (!$item = OpenBalanceItem::find($id)) {
            return $this->dispatch('error', 'Select item to delete not found. Refresh and try again.');
        }

        $deletePay = $this->open_balance->items->count() > 1 ? false : true;

        DB::beginTransaction();
        try {
            $item->delete();
            if ($deletePay) {
                $this->open_balance->delete();
            }
            DB::commit();
            $this->render();
            $this->dispatch('refresh');
            $this->dispatch('success', 'Successful removed an item.');
        } catch (\Throwable $th) {
            DB::rollBack();
            $this->dispatch('error', 'Failed, item was not removed. Refresh and try again.');
        }
    }

    public function importModal()
    {
        $this->dispatch('modal-show', 'import');
    }

    public function getTemplate()
    {
        $reader = new Html();
        $spreadsheet = $reader->loadFromString(view('storage-and-supply.openbalance.template', [
            'items' => Item::where('restockable', true)->get(),
            'stores' => $this->stores,
        ])->render());

        $borderStyle = [
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN, // Border style (e.g., THIN, THICK, DASHED)
                    'color' => ['argb' => Color::COLOR_BLACK], // Border color (black in this case)
                ],
            ],
        ];


        $sheet = $spreadsheet->getActiveSheet();
        $sheet->getStyle('A1:' . $sheet->getHighestColumn() . $sheet->getHighestRow())->applyFromArray($borderStyle);
        foreach (range('A', $sheet->getHighestColumn()) as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        // Write the spreadsheet to a file and prompt download
        $writer = new Xlsx($spreadsheet);
        $temp_file = tempnam(sys_get_temp_dir(), 'excel');
        $writer->save($temp_file);
        $name = 'hospi-template-' . strtotime(now());

        // Then return the file as a download in your controller
        return response()->download($temp_file, ".$name.xlsx")->deleteFileAfterSend(true);
    }

    public function import()
    {
        $this->validate([
            'open_balance.date' => 'required|date|before:tomorrow',
            'open_balance.description' => 'required|string',
        ]);

        $this->validate(['items_file' => 'required|mimes:xlsx,xls,csv']);

        $reader = \PhpOffice\PhpSpreadsheet\IOFactory::load($this->items_file->getRealPath());
        $worksheet = $reader->getActiveSheet();
        $rows = $worksheet->toArray();
        $items = [];

        DB::beginTransaction();
        try {
            for ($i = 7; $i < Coordinate::columnIndexFromString($worksheet->getHighestColumn(1)); $i++) {
                if (emptyOrNull($rows[0][$i]) || !($store = Subdepartment::where('name', trim($rows[0][$i]))->first())) {
                    // continue;
                    return $this->dispatch('error', "Store named $rows[0][$i] not found in the database, Add it through settings and re-upload the spreadsheet file.");
                }

                // Save open balance
                $this->open_balance->status = 'active';
                $this->open_balance->subdepartment_id = $store->id;
                $this->open_balance->save();

                foreach (collect($rows)->skip(2) as $key => $row) {
                    [$id, $item,, $buying_price, $batch, $expiry, $per_unit] = $row;

                    if (!$item = Item::find(intval(trim($id)))) {
                        continue;
                        // return $this->dispatch('error', "Item named $item->name in row $key + 1 not found in the database, fill it and re-upload the spreadsheet file.");
                    }
                    // Check if item already exists in open balance

                    if (itemBalance($this->open_balance->subdepartment_id, $item->id)) {
                        // continue;
                        // return $this->dispatch('error', "Failed, open balance was already set for item $item->name.");
                    }

                    if (!$buying_price) {
                        continue;
                        // return $this->dispatch('error', "No price found for item $item->name in row " . $key + 1 . ', fill it and re-upload the spreadsheet file.');
                    }
                    if (!$buying_price) {
                        continue;
                        // return $this->dispatch('error', "No buying price found for item $item->name in row " . $key + 1 . ', fill it and re-upload the spreadsheet file.');
                    }

                    $batch = trim($batch) ? trim($batch) : strtotime(now());

                    $per_unit = emptyOrNull($per_unit) ? 1 : intval(trim($per_unit));

                    if (!($units = intval(trim($row[$i])))) {
                        continue;
                    }

                    $items[] = [
                        'open_balance_id' => $this->open_balance->id,
                        'item_id' => $item->id,
                        'units' => $units,
                        'items_per_unit' => $per_unit,
                        'buying_price' => doubleval(trim($buying_price)),
                        'batch_no' => $batch,
                        'expiry_date' => $expiry,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];
                }
            }

            if (!count($items)) {
                $this->open_balance = new GRNOpenBalance([
                    'date' => $this->open_balance->date,
                    'description' => $this->open_balance->description,
                ]);
                return $this->dispatch('error', 'No importable items found in spreadsheet file. At least one record should be imported. Please check the spreadsheet file and try again. This happens when the uploaded spreadsheet file has no data at all or when the data is missing either units, name or buying price.');
            }

            foreach (array_chunk($items, 100) as $chunk) {
                DB::table('open_balance_items')->insert($chunk);
            }

            DB::commit();
            $this->dispatch('success', 'Items imported successfully.', 'import');
        } catch (\Throwable $th) {
            DB::rollBack();
            info('Failed to import items. ' . $th->getMessage());
            $this->dispatch('error', 'Failed to import items. Please try again.');
        }
    }

    public function loadItems($q)
    {
        return Item::whereRestockable(true)
            ->where('consultation_type', 'like', $this->cons_type)
            ->where('name', 'like', "%$q%")
            ->get(['id', 'code', 'name'])
            ->each(fn($itm) => $itm->name = "$itm->code: $itm->name");
    }

    public function resetFields()
    {
        //
    }

    protected function toValidate($form = 'open_balance')
    {
        return Arr::where($this->rules, function ($value, $key) use ($form) {
            return str($key)->startsWith("$form.");
        });
    }

    protected $rules = [
        'open_balance.date' => 'required|date|before:tomorrow',
        'open_balance.subdepartment_id' => 'required|numeric',
        'open_balance.description' => 'required|string',
        'open_balance.id' => 'nullable|numeric',
        'sel_item.name' => 'required|string',
        'sel_item.quantity' => 'required|numeric',
        'sel_item.per_unit' => 'required|numeric',
        'sel_item.buying_price' => 'required|numeric',
        'sel_item.expiry_date' => 'nullable|date|after:today',
        'sel_item.balance' => 'required|numeric',
        'sel_item.units' => 'required|numeric',
        'sel_item.unit' => 'required|string',
        'sel_item.batch_no' => 'nullable|string',
    ];
}
