<?php

namespace AppBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\Regex;
use AppBundle\Form\Type\CaptchaType;
use AppBundle\Validator\Constraints\CsrfToken;
use AppBundle\Validator\Constraints\Captcha;

class PasteType extends AbstractType {

  public function buildForm(FormBuilderInterface $builder, array $options) {
    $builder

      // CSRF nonce
      ->add('nonce', HiddenType::class, array(
        'data' => $options['nonce'],
        'required' => false,
        'mapped' => false,
        'constraints' => array(
          new CsrfToken(array(
            'csrf_token_id' => 'paste',
          )),
        ),
      ))

      // Paste
      ->add('paste', TextareaType::class, array(
        'attr' => array('placeholder' => 'Paste your text here'),
        'required' => false,
        'constraints' => array(
          new NotBlank(array('message' => 'Paste cannot be empty')),
          new Length(array(
            'max' => $options['body_max_length'],
            'maxMessage' => 'Encrypted paste cannot exceed X characters',
          )),
          new Regex(array(
            'pattern' => '#^(?:[A-Za-z0-9+/]{4})*(?:[A-Za-z0-9+/]{2}==|[A-Za-z0-9+/]{3}=)?$#',
            'message' => 'Encrypted paste appears invalid',
          )),
        )
      ))

      // Password
      ->add('password', PasswordType::class, array(
        'attr' => array('placeholder' => 'Protect with a password or generate one'),
        'required' => false,
        'mapped' => false,
      ))

      // Expiration
      ->add('expiration', ChoiceType::class, array(
        'choices' => array(
          'Burn After Reading' => 'once',
          '10 Minutes'         => '10min',
          '1 Hour'             => '1h',
          '1 Day'              => '1d',
          '1 Week'             => '1w',
          '1 Month'            => '1m',
          '1 Year'             => '1y',
          'Never Expires'      => 'never',
        ),
        'data' => 'never',
        'required' => false,
        'constraints' => array(
          new NotBlank(array('message' => 'Expiration cannot be blank')),
        ),
      ))

      // Captcha
      ->add('captcha', CaptchaType::class, array(
        'label' => 'Captcha',
        'attr' => array('placeholder' => 'Type the CAPTCHA text here'),
        'mapped' => false,
        'required' => false,
        'constraints' => array(
          new Captcha(),
        ),
      ));
  }


  public function configureOptions(OptionsResolver $resolver) {
    $resolver->setDefaults(array(
      'csrf_protection' => false, // Done manually to ensure AJAX cooperation
      'error_bubbling'  => true,
      'body_max_length' => 3145728,
      'nonce'           => '',
    ));
  }


  public function getName() {
    return 'paste_form';
  }

}

// EOF
