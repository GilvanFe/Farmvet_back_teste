<?php
declare(strict_types=1);

namespace App\Controller;

/**
 * Catmat Controller
 *
 * @property \App\Model\Table\CatmatTable $Catmat
 */
class CatmatController extends AppController
{
    /**
     * Index method
     *
     * @return \Cake\Http\Response|null|void Renders view
     */
    public function index()
    {
        $query = $this->Catmat->find()
            ->contain(['Item']);
        $catmat = $this->paginate($query);

        $this->set(compact('catmat'));
    }

    /**
     * View method
     *
     * @param string|null $id Catmat id.
     * @return \Cake\Http\Response|null|void Renders view
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function view($id = null)
    {
        $catmat = $this->Catmat->get($id, contain: ['Item']);
        $this->set(compact('catmat'));
    }

    /**
     * Add method
     *
     * @return \Cake\Http\Response|null|void Redirects on successful add, renders view otherwise.
     */
    public function add()
    {
        $catmat = $this->Catmat->newEmptyEntity();
        if ($this->request->is('post')) {
            $catmat = $this->Catmat->patchEntity($catmat, $this->request->getData());
            if ($this->Catmat->save($catmat)) {
                $this->Flash->success(__('The catmat has been saved.'));

                return $this->redirect(['action' => 'index']);
            }
            $this->Flash->error(__('The catmat could not be saved. Please, try again.'));
        }
        $item = $this->Catmat->Item->find('list', limit: 200)->all();
        $this->set(compact('catmat', 'item'));
    }

    /**
     * Edit method
     *
     * @param string|null $id Catmat id.
     * @return \Cake\Http\Response|null|void Redirects on successful edit, renders view otherwise.
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function edit($id = null)
    {
        $catmat = $this->Catmat->get($id, contain: []);
        if ($this->request->is(['patch', 'post', 'put'])) {
            $catmat = $this->Catmat->patchEntity($catmat, $this->request->getData());
            if ($this->Catmat->save($catmat)) {
                $this->Flash->success(__('The catmat has been saved.'));

                return $this->redirect(['action' => 'index']);
            }
            $this->Flash->error(__('The catmat could not be saved. Please, try again.'));
        }
        $item = $this->Catmat->Item->find('list', limit: 200)->all();
        $this->set(compact('catmat', 'item'));
    }
}
