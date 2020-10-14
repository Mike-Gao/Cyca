<?php

namespace App\Http\Controllers;

use App\Facades\ThemeManager;
use App\Services\Import\Importer;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return view('home');
    }

    /**
     * Show application's about page'.
     *
     * @return \Illuminate\Http\Response
     */
    public function about()
    {
        return view('account.about');
    }

    /**
     * Show user's account page
     */
    public function account()
    {
        return view('account.my_account');
    }

    /**
     * Show user's password update page
     */
    public function password()
    {
        return view('account.password');
    }

    /**
     * Theme selection page
     */
    public function theme()
    {
        $availableThemes = ThemeManager::listAvailableThemes();

        return view('account.themes')->with(['availableThemes' => $availableThemes]);
    }

    /**
     * Save theme to user's profile
     */
    public function setTheme(Request $request)
    {
        if (!ThemeManager::themeExists($request->input('theme'))) {
            abort(422);
        }

        $request->user()->theme = $request->input('theme');
        $request->user()->save();
    }

    /**
     * Manage user's highlights
     */
    public function highlights()
    {
        return view('account.highlights');
    }

    /**
     * Export user's data
     */
    public function export(Request $request)
    {
        $root      = $request->user()->folders()->ofType('root')->first();
        $rootArray = [
            'documents' => [],
            'folders'   => $this->exportTree($root->children()->get()),
        ];

        foreach ($root->documents()->get() as $document) {
            $documentArray = [
                'url'   => $document->url,
                'feeds' => [],
            ];

            foreach ($document->feeds()->get() as $feed) {
                $documentArray['feeds'] = [
                    'url'        => $feed->url,
                    'is_ignored' => $feed->is_ignored,
                ];
            }

            $rootArray['documents'][] = $documentArray;
        }

        $data = [
            'highlights' => $request->user()->highlights()->select(['expression', 'color'])->get(),
            'bookmarks'  => $rootArray,
        ];

        return response()->streamDownload(function () use ($data) {
            echo json_encode($data);
        }, sprintf('%s - Export.json', $request->user()->name), [
            'Content-Type' => 'application/x-json',
        ]);
    }

    /**
     * Export a single tree branch
     */
    protected function exportTree($folders)
    {
        $array = [];

        foreach ($folders as $folder) {
            $folderArray = [
                'title'     => $folder->title,
                'documents' => [],
                'folders'   => [],
            ];

            foreach ($folder->documents()->get() as $document) {
                $documentArray = [
                    'url'   => $document->url,
                    'feeds' => [],
                ];

                foreach ($document->feeds()->get() as $feed) {
                    $documentArray['feeds'][] = [
                        'url'        => $feed->url,
                        'is_ignored' => $feed->is_ignored,
                    ];
                }

                $folderArray['documents'][] = $documentArray;
            }

            $folderArray['folders'] = $this->exportTree(($folder->children()->get()));

            $array[] = $folderArray;
        }

        return $array;
    }

    /**
     * Show the import form
     */
    public function showImportForm()
    {
        return view('account.import');
    }

    /**
     * Import a file
     */
    public function import(Request $request)
    {
        (new Importer())->fromRequest($request)->import();

        return ['ok' => true];
    }
}
