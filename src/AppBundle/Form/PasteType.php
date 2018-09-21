<?php

namespace AppBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\Regex;
use AppBundle\Form\Type\CaptchaType;
use AppBundle\Validator\Constraints\Captcha;

class PasteType extends AbstractType {

  public function buildForm(FormBuilderInterface $builder, array $options) {
    $builder

      // Paste
      ->add('paste', TextareaType::class, array(
        'attr' => array('placeholder' => 'Paste your text here'),
        'required' => true,
        'constraints' => array(
          new NotBlank(array('message' => 'Paste cannot be empty')),
/*
          new Length(array(
            'max' => 16,
            'maxMessage' => 'Encrypted paste cannot exceed X characters',
          )),
*/
          new Regex(array(
            'pattern' => '#^(?:[A-Za-z0-9+/]{4})*(?:[A-Za-z0-9+/]{2}==|[A-Za-z0-9+/]{3}=)?$#',
            'message' => 'Encrypted paste appears invalid',
          )),
        )
      ))

      // Password
      ->add('password', PasswordType::class, array(
        'label' => 'Password',
        'attr' => array('placeholder' => 'Protect with a password or generate one'),
        'required' => false,
      ))

      // Expiration
      ->add('expiration', ChoiceType::class, array(
        'attr' => array('data-label' => 'Hide login'),
        'label' => 'Hide login',
        'help' => 'Login discretely (only staff can see you logged in)',
        'required' => false,
      ))

      // Captcha
      ->add('captcha', CaptchaType::class, array(
        'label' => 'Captcha',
        'attr' => array('placeholder' => 'CAPTCHA text'),
        'mapped' => false,
        'constraints' => array(
          new Captcha(),
        ),
      ));
  }


  public function configureOptions(OptionsResolver $resolver) {
    $resolver->setDefaults(array(
      'csrf_token_id'  => 'paste',
      'error_bubbling' => true,
    ));
  }


  public function getName() {
    return 'paste_form';
  }

}

// EOF
