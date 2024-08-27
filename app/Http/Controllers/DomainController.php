<?php

namespace App\Http\Controllers;

use App\Jobs\makeBackup;
use App\Models\Domain;
use Carbon\Carbon;
use Illuminate\Http\Request;

class DomainController extends Controller
{
    public function index()
    {

    }

    public function scheduleEvent($domain)
    {
        // prendiamo l'orario previsto
        $scheduleTime = Carbon::parse($domain->backup_rate_time)->format('H:i');
        // confrontiamolo con ora
        $now = Carbon::now('Europe/Paris')->format('H:i');

        if ($scheduleTime > $now) {
            $delay = Carbon::createFromFormat('H:i', $scheduleTime)->diffInSeconds($now);
        } else {
            $delay = Carbon::createFromFormat('H:i', $scheduleTime)->addDay()->diffInSeconds($now);
        }
        // chiamiamo il job con il deley necessario per richiedere il backup
        makeBackup::dispatch($domain->id)->delay($delay);
    }

    public function store(Request $request)
    {

        $request->validate([
            'user_name' => 'required',
            'domain_name' => 'required',
            'database_name' => 'required',
            'ip' => 'required',
            'password' => 'required',
            'backup_rate_time' => 'required',
        ]);

        $newDomain = Domain::create($request->all());

        //dopo che abbiamo creato il dominio e l'abbiamo salvato su db, prendiamolo e passamolo alla funziuone che si occuperà di settare il cronjob
        $this->scheduleEvent($newDomain);

        // per creare un backup al momento e testare chiama direttamente il job
        // makeBackup::dispatch($newDomain->id);
    }
    public function update(Request $request)
    {
        $request->validate([
            'backup_rate_time' => 'required',
            'domain_id' => 'required'
        ]);
        // dd($request->all());
        $domainToFind = Domain::where('id', $request->domain_id)->first();

        $data = $request->all();

        $domainToFind->backup_rate_time = $data['backup_rate_time'];
        $domainToFind->save();


        return response('ok');
        // dd($domainToFind->backup_rate_time);

    }
    // creazione di un backup all'istante per ogni dominio inserito su db
    public function massCreation()
    {

        $domains = Domain::all();

        foreach ($domains as $domain) {

            makeBackup::dispatch($domain->id);
        }

        return response('ok');
    }
}
