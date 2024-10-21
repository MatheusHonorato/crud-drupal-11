<?php

namespace Drupal\cases\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Database\Database;
use Drupal\file\Entity\File;

class CaseForm extends FormBase
{
    public function getFormId()
    {
        return 'case_form';
    }

    public function buildForm(array $form, FormStateInterface $form_state)
    {
        $conn = Database::getConnection();
        $result = [];

        if (isset($_GET['id'])) {
            $query = $conn->select('cases', 'c')
                ->condition('id', $_GET['id'])
                ->fields('c');
            $result = $query->execute()->fetchAssoc();
        }

        if (isset($result->image) && !empty($result->image)) {
            $image_render_array = [
                '#theme' => 'image',
                '#uri' => $result->image,
                '#alt' => $this->t('Image preview'),
                '#attributes' => [
                  'class' => ['image-preview'],
                  'style' => 'width: 100px;',
                ],
              ];

            $form['current_image_preview'] = [
                '#type' => 'item',
                '#title' => $this->t('Imagem atual'),
                '#markup' => \Drupal::service('renderer')->render($image_render_array),
            ];
        }

        $form['image'] = [
            '#type' => 'managed_file',
            '#title' => $this->t('Imagem'),
            '#description' => $this->t('Faça upload de uma imagem.'),
            '#upload_location' => 'public://cases',
            '#required' => true,
            '#default_value' => isset($result->image_fid) ? [$result->image_fid] : [],
        ];

        $form['title'] = [
            '#type' => 'textfield',
            '#title' => $this->t('Título'),
            '#default_value' => isset($result->title) ? $result->title : '',
            '#maxlength' => 255,
            '#required' => true,
        ];

        $form['description'] = [
            '#type' => 'textarea',
            '#title' => $this->t('Descrição'),
            '#default_value' => isset($result->description) ? $result->description : '',
            '#required' => true,
        ];

        $form['submit'] = [
            '#type' => 'submit',
            '#value' => $this->t('Salvar'),
        ];

        return $form;
    }

    public function validateForm(array &$form, FormStateInterface $form_state)
    {
        $image_fid = $form_state->getValue('image');
        if (empty($image_fid) || !is_array($image_fid) || empty($image_fid[0])) {
            $form_state->setErrorByName('image', $this->t('Por favor, envie uma imagem válida.'));
        }

        $title = $form_state->getValue('title');
        if (empty($title)) {
            $form_state->setErrorByName('title', $this->t('O título é obrigatório.'));
        } elseif (strlen($title) > 255) {
            $form_state->setErrorByName('title', $this->t('O título não pode exceder 255 caracteres.'));
        }

        $description = $form_state->getValue('description');
        if (empty($description)) {
            $form_state->setErrorByName('description', $this->t('A descrição é obrigatória.'));
        }
    }

    /**
     * {@inheritdoc}
     */
    public function submitForm(array &$form, FormStateInterface $form_state)
    {
        $image_fid = $form_state->getValue('image')[0];
        $file = File::load($image_fid);
        $image_path = '';

        if ($file) {
            $file->setPermanent();
            $file->save();
            $base_url = \Drupal::request()->getSchemeAndHttpHost();
            $image_path = $base_url . '/sites/default/files/cases/' . explode("/", $file->getFileUri())[3];
        }

        $title = $form_state->getValue('title');
        $description = $form_state->getValue('description');

        $fields = [
            'image' => $image_path ?? '',
            'title' => $title,
            'description' => $description,
        ];

        $conn = Database::getConnection();
        if (isset($_GET['id'])) {
            $conn->update('cases')
                ->fields($fields)
                ->condition('id', $_GET['id'])
                ->execute();

            \Drupal::messenger()->addStatus($this->t('Atualizado com sucesso.'));
        } else {
            $conn->insert('cases')
                ->fields($fields)
                ->execute();

            \Drupal::messenger()->addStatus($this->t('Criado com sucesso.'));
        }

        $form_state->setRedirect('cases.list');
    }
}
