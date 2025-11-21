<?php
namespace Model\Storage;
use \Illuminate\Support\Facades\DB as DB;
use \Illuminate\Database\Eloquent\Model as Eloquent;
use \Illuminate\Support\Facades\Facade as Facade;

class Conf extends Eloquent
{
	public $timestamps = false;
	public function __construct(){
	}

	public function getCurrConnection(){
		return $this->getConnection();
	}

	public static function factory($nama_class){
		$new_class = '\Model\\Storage\\'.$nama_class;
		return new $new_class;
	}

	public function getDate() {
			return $this->hydrateRaw('select getdate() waktu, left(cast(getdate() as date),10) tanggal, left(cast(getdate() as time),8) jam', array())->first()->toArray();
	}

	public function getNextIdentity()
	{
		$next_id = $this -> max($this->primaryKey) + 1;
		return $next_id;
	}

	public function getNextId(){
		$id = $this->whereRaw("SUBSTRING(".$this->primaryKey.",4,4) = cast(right(year(current_timestamp),2) as char(2))+replace(str(month(getdate()),2),' ',0)")
								->selectRaw("'".$this->kodeTable."'+right(year(current_timestamp),2)+replace(str(month(getdate()),2),' ',0)+replace(str(substring(coalesce(max(".$this->primaryKey."),'000'),8,3)+1,3), ' ', '0') as nextId")
								->first();
		return $id->nextId;
	}

	public function getNextIdRibuan(){
		$id = $this->whereRaw("SUBSTRING(".$this->primaryKey.",4,4) = cast(right(year(current_timestamp),2) as char(2))+replace(str(month(getdate()),2),' ',0)")
								->selectRaw("'".$this->kodeTable."'+right(year(current_timestamp),2)+replace(str(month(getdate()),2),' ',0)+replace(str(substring(coalesce(max(".$this->primaryKey."),'0000'),8,4)+1,4), ' ', '0') as nextId")
								->first();
		return $id->nextId;
	}

	public function getNextId_Ribuan2Huruf(){
		$id = $this->whereRaw("SUBSTRING(".$this->primaryKey.",3,4) = cast(right(year(current_timestamp),2) as char(2))+replace(str(month(getdate()),2),' ',0)")
								->selectRaw("'".$this->kodeTable."'+right(year(current_timestamp),2)+replace(str(month(getdate()),2),' ',0)+replace(str(substring(coalesce(max(".$this->primaryKey."),'0000'),8,3)+1,4), ' ', '0') as nextId")
								->first();
		return $id->nextId;
	}

	public function getNextDocNum($kode=''){
		$len_docNum = strlen($kode) + 2;

		$seq_docNum = ($len_docNum + 7)-1;

		$_date = $this->getDate()['tanggal'];
		$_dateNew = dateAlpha($_date);

		$id = $this->whereRaw("SUBSTRING(".$this->docNum.",".$len_docNum.",5) = '".$_dateNew."'")
				   ->selectRaw("'".$kode."'+'/'+'".$_dateNew."'+'-'+replace(str(substring(coalesce(max(".$this->docNum."),'00'),".$seq_docNum.",2)+1,2), ' ', '0') as nextId")
				   ->first();

		return $id->nextId;
	}

	public function runSp($sp, $bind): void
    {
        // --- Synchronous execution happens *inside* the background job ---

        $sqlQuery = $sp;

        // Execute the query and get raw results (stdClass objects)
        $rawResults = DB::select($sqlQuery, $bind);

        // Hydrate those raw results into full Eloquent Models
        $query = $this->hydrateRaw($sqlQuery, $bind);

        // --- Now you can work with $users as Eloquent models asynchronously ---

        // foreach ($users as $user) {
        //     // Perform background tasks with the hydrated model
        //     \Log::info('Processing user ID: ' . $user->id);
        //     // Example: $user->update(['processed' => true]);
        // }
    }
}