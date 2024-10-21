<?php

namespace Drupal\cases\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Url;
use Drupal\Core\Link;
use Symfony\Component\HttpFoundation\JsonResponse;
use Drupal\Core\Database\Database;

class CasesController extends ControllerBase
{
    public function list()
    {
        $header_table = [
          'id' => t('Id'),
          'image' => t('Imagem'),
          'title' => t('Título'),
          'description' => t('Descrição'),
          'edit' => t('Editar'),
          'delete' => t('Remover'),
        ];

        $query = \Drupal::database()->select('cases', 'c');
        $query->fields('c', ['id', 'image', 'title', 'description']);
        $results = $query->execute()->fetchAll();

        $rows =  [];

        foreach ($results as $result) {
            $edit = Url::fromUserInput('/cases/form?id=' . $result->id);
            $delete = Url::fromRoute('cases.delete', ['cid' => $result->id]);

            $image_render_array = [
              '#theme' => 'image',
              '#uri' => $result->image,
              '#alt' => $this->t('Image preview'),
              '#attributes' => [
                'class' => ['image-preview'],
                'style' => 'width: 100px;',
              ],
            ];

            $rows[] = [
              'id' => $result->id,
              'image' => \Drupal::service('renderer')->render($image_render_array),
              'title' => $result->title,
              'description' => $result->description,
              Link::fromTextAndUrl('Editar', $edit)->toString(),
              Link::fromTextAndUrl('Remover', $delete)->toString(),
            ];
        }

        $create_url = Url::fromUserInput('/cases/form');
        $create_link = Link::fromTextAndUrl($this->t('Criar Novo Case'), $create_url)->toString();

        $form['create_link'] = [
          '#markup' => $create_link,
        ];

        $form['table'] = [
          '#type' => 'table',
          '#header' => $header_table,
          '#rows' => $rows,
          '#empty' => t('Nenhum registro encontrado'),
          '#attributes' => [
            'style' => 'width: 100%; text-align: left;',
          ],
        ];

        return $form;
    }

    public function getCase($id)
    {
        $conn = Database::getConnection();
        $query = $conn->select('cases', 'c')
                      ->condition('id', $id)
                      ->fields('c');
        $record = $query->execute()->fetchAssoc();

        if ($record) {
            $data = [
              'id' => $record['id'],
              'image' => $record['image'],
              'title' => $record['title'],
              'description' => $record['description'],
            ];

            return new JsonResponse($data);
        }

        return new JsonResponse(['message' => 'Case not found'], 404);
    }
}
