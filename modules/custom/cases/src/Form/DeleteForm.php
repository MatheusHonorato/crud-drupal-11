<?php

namespace Drupal\cases\Form;

use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Form\ConfirmFormBase;
use Drupal\Core\Url;
use Drupal\Core\Database\Database;

class DeleteForm extends ConfirmFormBase
{
    protected $id;

    public function getFormId()
    {
        return 'cases_delete_form';
    }

    public function getQuestion()
    {
        return $this->t('Deseja remover este usuário?');
    }

    public function getCancelUrl()
    {
        return new Url('cases.list');
    }

    public function getDescription()
    {
        return $this->t('Tem certeza que deseja excluir este item? Esta ação não pode ser desfeita.');
    }

    public function getConfirmText()
    {
        return $this->t('Remover');
    }

    public function getCancelText()
    {
        return $this->t('Cancelar');
    }

    public function buildForm(array $form, FormStateInterface $form_state, $cid = null)
    {
        $this->id = $cid;

        return parent::buildForm($form, $form_state);
    }

    public function submitForm(array &$form, FormStateInterface $form_state)
    {
        $connection = Database::getConnection();
        $connection->delete('cases')
              ->condition('id', $this->id)
              ->execute();

        \Drupal::messenger()->addStatus($this->t('Removido com sucesso'));
        $form_state->setRedirect('cases.list');
    }
}
