<?php

namespace App\Controller;

use App\Entity\Contrato;
use App\Repository\ContratoRepository;
use App\Repository\CobranzaRepository;
use App\Repository\CuotaRepository;
use App\Repository\PagoRepository;
use App\Repository\PagoCuotasRepository;
use App\Repository\ContratoRolRepository;
use App\Repository\ContratoAnexoRepository;
use App\Repository\UsuarioRepository;
use App\Repository\ModuloPerRepository;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Csv;
use PhpOffice\PhpSpreadsheet\Writer\Xls;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/export_gerencia")
 */
class ExportGerenciaController extends AbstractController
{
    /**
     * @Route("/", name="export_gerencia_index")
     */
    public function index(ModuloPerRepository $moduloPerRepository): Response
    {
        $this->denyAccessUnlessGranted('view','export_gerencia');
        $user=$this->getUser();
        $pagina=$moduloPerRepository->findOneByName('export_gerencia',$user->getEmpresaActual());
        return $this->render('export_gerencia/index.html.twig', [
            'pagina' => $pagina->getNombre(),
        ]);
    }

    /**
     * @Route("/contrato", name="export_gerencia_contrato", methods={"GET","POST"})
     */
    public function contrato(ContratoRepository $contratoRepository): Response
    {
        $fileName="contratos.csv";
        $titulo = "Contratos TBL";
        
        $query=$contratoRepository->findAll();
        $spreadSheet=new Spreadsheet();

        /* @var $sheet \PhpOffice\PhpSpreadsheet\Writer\Xlsx\Worksheet */
        $sheet = $spreadSheet->getActiveSheet();
        $sheet->setCellValue('A1', 'id');
        $sheet->setCellValue('B1', 'nombre');
        $sheet->setCellValue('C1', 'monto_contrato');
        $sheet->setCellValue('D1', 'cuotas');
        $sheet->setCellValue('E1', 'valor_cuota');
        $sheet->setCellValue('F1', 'fecha_creacion');
        $sheet->setCellValue('G1', 'is_finalizado');
        $sheet->setCellValue('H1', 'id_lote_id');
        $sheet->setCellValue('I1', 'Folio');
       
        $sheet = $spreadSheet->getActiveSheet();
        $i=2;
        foreach($query as $contrato){

            $sheet->setCellValue("A$i",$contrato->getId());
            $sheet->setCellValue("B$i",$contrato->getNombre());
            $sheet->setCellValue("C$i",$contrato->getMontoContrato());
            $sheet->setCellValue("D$i",$contrato->getCuotas());
            $sheet->setCellValue("E$i",$contrato->getFechaCreacion());
            $sheet->setCellValue("F$i",$contrato->getIsFinalizado());
            if($contrato->getIdLote()!= null)
                $sheet->setCellValue("F$i",$contrato->getIdLote()->getId());
            $sheet->setCellValue("F$i",$contrato->getFolio());
            $i++;
        }

        $sheet->setTitle($titulo);
 
        // Create your Office 2007 Excel (XLSX Format)
        $writer = new Csv($spreadSheet);
        $writer->setDelimiter('|');
        $writer->setEnclosure('');
            
        // Create a Temporary file in the system
        
        $temp_file = tempnam(sys_get_temp_dir(), $fileName);
    
        // Create the excel file in the tmp directory of the system
        $writer->save($temp_file);
    
        // Return the excel file as an attachment
        return $this->file($temp_file, $fileName, ResponseHeaderBag::DISPOSITION_ATTACHMENT);
        
    }

    /**
     * @Route("/cobranza", name="export_gerencia_cobranza", methods={"GET","POST"})
     */
    public function cobranza(CobranzaRepository $cobranzaRepository): Response
    {
        $fileName="cobranza.csv";
        $titulo = "Cobranzas TBL";
        
        $query=$cobranzaRepository->findAll();
        $spreadSheet=new Spreadsheet();

        /* @var $sheet \PhpOffice\PhpSpreadsheet\Writer\Xlsx\Worksheet */
        $sheet = $spreadSheet->getActiveSheet();
        $sheet->setCellValue('A1', 'id');
        $sheet->setCellValue('B1', 'funcion_id');
        $sheet->setCellValue('C1', 'respuesta_id');
        $sheet->setCellValue('D1', 'cuota_id');
        $sheet->setCellValue('E1', 'fecha_hora');
        $sheet->setCellValue('F1', 'fecha_compromiso');
        $sheet->setCellValue('G1', 'observacion');
        $sheet->setCellValue('H1', 'is_nulo');
        $sheet->setCellValue('I1', 'usuario_registro');
        $sheet->setCellValue('J1', 'fecha');
        $sheet->setCellValue('K1', 'contrato_id');
       
        $sheet = $spreadSheet->getActiveSheet();
        $i=2;
        foreach($query as $cobranza){

            $sheet->setCellValue("A$i",$cobranza->getId());
            if($cobranza->getFuncion()!=null)
                $sheet->setCellValue("B$i",$cobranza->getFuncion()->getId());
            if($cobranza->getRespuesta()!=null)
                $sheet->setCellValue("C$i",$cobranza->getRespuesta()->getId());
            $sheet->setCellValue("D$i",'null');
            $sheet->setCellValue("E$i",$cobranza->getFechaHora());
            if($cobranza->getFechaCompromiso()!=null)
                $sheet->setCellValue("F$i",$cobranza->getFechaCompromiso());
            $sheet->setCellValue("G$i",$cobranza->getObservacion());
            if($cobranza->getIsNulo()!=null)
                $sheet->setCellValue("H$i",$cobranza->getIsNulo());
            $sheet->setCellValue("I$i",$cobranza->getUsuarioRegistro());
            $sheet->setCellValue("J$i",$cobranza->getFecha());
            $sheet->setCellValue("K$i",$cobranza->getContrato()->getId());
            $i++;
        }
        $sheet->setTitle($titulo);
 
        // Create your Office 2007 Excel (XLSX Format)
        $writer = new Csv($spreadSheet);
        $writer->setDelimiter('|');
        $writer->setEnclosure('');
            
        // Create a Temporary file in the system
        
        $temp_file = tempnam(sys_get_temp_dir(), $fileName);
    
        // Create the excel file in the tmp directory of the system
        $writer->save($temp_file);
    
        // Return the excel file as an attachment
        return $this->file($temp_file, $fileName, ResponseHeaderBag::DISPOSITION_ATTACHMENT);
    }

//Cuota Hans
/**
     * @Route("/cuota", name="export_gerencia_cuota", methods={"GET","POST"})
     */
    public function cuota(CuotaRepository $cuotaRepository): Response
    {
        $fileName="cuota.csv";
        $titulo = "Cuota TBL";
        
        $query=$cuotaRepository->findAll();
        $spreadSheet=new Spreadsheet();

        /* @var $sheet \PhpOffice\PhpSpreadsheet\Writer\Xlsx\Worksheet */
        $sheet = $spreadSheet->getActiveSheet();
        $sheet->setCellValue('A1', 'id');
        $sheet->setCellValue('B1', 'usuario_anulacion_id');
        $sheet->setCellValue('C1', 'numero');
        $sheet->setCellValue('D1', 'fecha_pago');
        $sheet->setCellValue('E1', 'monto');
        $sheet->setCellValue('F1', 'pagado');
        $sheet->setCellValue('G1', 'anular');
        $sheet->setCellValue('H1', 'fecha_anulacion');
        $sheet->setCellValue('I1', 'contrato_id');
        $sheet->setCellValue('J1', 'is_multa');
        $sheet->setCellValue('K1', 'anexo_id');
        $sheet->setCellValue('L1', 'invoice_id');
       
        $sheet = $spreadSheet->getActiveSheet();
        $i=2;
        foreach($query as $cuota){

            $sheet->setCellValue("A$i",$cuota->getId());
            $sheet->setCellValue("B$i",'null');
            $sheet->setCellValue("C$i",$cuota->getNumero());
            $sheet->setCellValue("D$i",$cuota->getFechaPago());
            $sheet->setCellValue("E$i",$cuota->getMonto());
            if($cuota->getPagado()!=null)
                $sheet->setCellValue("F$i",$cuota->getPagado());
            if($cuota->getAnular()!=null)
                $sheet->setCellValue("G$i",$cuota->getAnular());
            if($cuota->getFechaAnulacion()!=null)
                $sheet->setCellValue("H$i",$cuota->getFechaAnulacion());
            $sheet->setCellValue("I$i",$cuota->getContrato()->getId());
            if($cuota->getIsMulta()!=null)
                $sheet->setCellValue("J$i",$cuota->getIsMulta());
            if($cuota->getAnexo()!=null)
                $sheet->setCellValue("K$i",$cuota->getAnexo()->getId());
            if($cuota->getInvoiceId()!=null)
                $sheet->setCellValue("L$i",$cuota->getInvoiceId());
            $i++;
        }

        $sheet->setTitle($titulo);
 
        // Create your Office 2007 Excel (XLSX Format)
        $writer = new Csv($spreadSheet);
        $writer->setDelimiter('|');
        $writer->setEnclosure('');
            
        // Create a Temporary file in the system
        
        $temp_file = tempnam(sys_get_temp_dir(), $fileName);
    
        // Create the excel file in the tmp directory of the system
        $writer->save($temp_file);
    
        // Return the excel file as an attachment
        return $this->file($temp_file, $fileName, ResponseHeaderBag::DISPOSITION_ATTACHMENT);    
    }

//Pago Hans
/**
     * @Route("/pago", name="export_gerencia_pago", methods={"GET","POST"})
     */
    public function pago(PagoRepository $pagoRepository): Response
    {
        $fileName="pago.csv";
        $titulo = "Pago TBL";
        
        $query=$pagoRepository->findAll();
        $spreadSheet=new Spreadsheet();

        /* @var $sheet \PhpOffice\PhpSpreadsheet\Writer\Xlsx\Worksheet */
        $sheet = $spreadSheet->getActiveSheet();
        $sheet->setCellValue('A1', 'id');
        $sheet->setCellValue('B1', 'pago_tipo_id');
        $sheet->setCellValue('C1', 'pago_canal_id');
        $sheet->setCellValue('D1', 'usuario_registro_id');
        $sheet->setCellValue('E1', 'monto');
        $sheet->setCellValue('F1', 'boleta');
        $sheet->setCellValue('G1', 'observacion');
        $sheet->setCellValue('H1', 'fecha_pago');
        $sheet->setCellValue('I1', 'hora_pago');
        $sheet->setCellValue('J1', 'fecha_registro');
        $sheet->setCellValue('K1', 'cuenta_corriente_id');
        $sheet->setCellValue('L1', 'fecha_ingreso');
        $sheet->setCellValue('M1', 'ncomprobante');
        $sheet->setCellValue('N1', 'comprobante');
        $sheet->setCellValue('O1', 'usuario_anulacion_id');
        $sheet->setCellValue('P1', 'anulado');
        $sheet->setCellValue('Q1', 'fecha_anulacion');
       
        $sheet = $spreadSheet->getActiveSheet();
        $i=2;
        foreach($query as $pago){

            $sheet->setCellValue("A$i",$pago->getId());
            $sheet->setCellValue("B$i",$pago->getPagoTipo()->getId());
            $sheet->setCellValue("C$i",$pago->getPagoCanal()->getId());
            $sheet->setCellValue("D$i",$pago->getUsuarioRegistro()->getId());
            $sheet->setCellValue("E$i",$pago->getMonto());
            if($pago->getBoleta()!=null)
                $sheet->setCellValue("F$i",$pago->getBoleta());
            if($pago->getObservacion()!=null)
                $sheet->setCellValue("G$i",$pago->getObservacion());
            $sheet->setCellValue("H$i",$pago->getFechaPago());
            $sheet->setCellValue("I$i",$pago->getHoraPago());
            $sheet->setCellValue("J$i",$pago->getFechaRegistro());
            $sheet->setCellValue("K$i",$pago->getCuentaCorriente()->getId());
            if($pago->getFechaIngreso()!=null)
                $sheet->setCellValue("L$i",$pago->getFechaIngreso());
            if($pago->getNcomprobante()!=null)
                $sheet->setCellValue("M$i",$pago->getNcomprobante());
            if($pago->getComprobante()!=null)
                $sheet->setCellValue("N$i",$pago->getComprobante());
            if($pago->getUsuarioAnulacion()!=null)    
                $sheet->setCellValue("O$i",$pago->getUsuarioAnulacion()->getId());
            if($pago->getAnulado()!=null)    
                $sheet->setCellValue("P$i",$pago->getAnulado());
            if($pago->getFechaAnulacion()!=null)
                $sheet->setCellValue("Q$i",$pago->getFechaAnulacion());
            $i++;
        }

        $sheet->setTitle($titulo);
 
        // Create your Office 2007 Excel (XLSX Format)
        $writer = new Csv($spreadSheet);
        $writer->setDelimiter('|');
        $writer->setEnclosure('');
            
        // Create a Temporary file in the system
        
        $temp_file = tempnam(sys_get_temp_dir(), $fileName);
    
        // Create the excel file in the tmp directory of the system
        $writer->save($temp_file);
    
        // Return the excel file as an attachment
        return $this->file($temp_file, $fileName, ResponseHeaderBag::DISPOSITION_ATTACHMENT);    
    }

//PagoCuotas Hans
/**
     * @Route("/pagocuotas", name="export_gerencia_pagocuotas", methods={"GET","POST"})
     */
    public function pagocuotas(PagoCuotasRepository $pagoCuotasRepository): Response
    {
        $fileName="pago_cuotas.csv";
        $titulo = "PagoCuotas TBL";
        
        $query=$pagoCuotasRepository->findAll();
        $spreadSheet=new Spreadsheet();

        /* @var $sheet \PhpOffice\PhpSpreadsheet\Writer\Xlsx\Worksheet */
        $sheet = $spreadSheet->getActiveSheet();
        $sheet->setCellValue('A1', 'id');
        $sheet->setCellValue('B1', 'pago_id');
        $sheet->setCellValue('C1', 'cuota_id');
        $sheet->setCellValue('D1', 'monto');
       
        $sheet = $spreadSheet->getActiveSheet();
        $i=2;
        foreach($query as $pagoCuotas){

            $sheet->setCellValue("A$i",$pagoCuotas->getId());
            $sheet->setCellValue("B$i",$pagoCuotas->getPago()->getId());
            $sheet->setCellValue("C$i",$pagoCuotas->getCuota()->getId());
            $sheet->setCellValue("D$i",$pagoCuotas->getMonto());
            $i++;
        }

        $sheet->setTitle($titulo);
 
        // Create your Office 2007 Excel (XLSX Format)
        $writer = new Csv($spreadSheet);
        $writer->setDelimiter('|');
        $writer->setEnclosure('');
            
        // Create a Temporary file in the system
        
        $temp_file = tempnam(sys_get_temp_dir(), $fileName);
    
        // Create the excel file in the tmp directory of the system
        $writer->save($temp_file);
    
        // Return the excel file as an attachment
        return $this->file($temp_file, $fileName, ResponseHeaderBag::DISPOSITION_ATTACHMENT);    
    }

//ContratoRol Hans
/**
     * @Route("/contratorol", name="export_gerencia_contratorol", methods={"GET","POST"})
     */
    public function contratorol(ContratoRolRepository $ContratoRolRepository): Response
    {
        $fileName="contrato_rol.csv";
        $titulo = "ContratoRol TBL";
        
        $query=$ContratoRolRepository->findAll();
        $spreadSheet=new Spreadsheet();

        /* @var $sheet \PhpOffice\PhpSpreadsheet\Writer\Xlsx\Worksheet */
        $sheet = $spreadSheet->getActiveSheet();
        $sheet->setCellValue('A1', 'id');
        $sheet->setCellValue('B1', 'juzgado_id');
        $sheet->setCellValue('C1', 'contrato_id');
        $sheet->setCellValue('D1', 'nombre_rol');
        $sheet->setCellValue('E1', 'institucion_acreedora');
        $sheet->setCellValue('F1', 'abogado_id');
       
        $sheet = $spreadSheet->getActiveSheet();
        $i=2;
        foreach($query as $contratorol){

            $sheet->setCellValue("A$i",$contratorol->getId());
            if($contratorol->getJuzgado()!=null)
                $sheet->setCellValue("B$i",$contratorol->getJuzgado()->getId());
            $sheet->setCellValue("C$i",$contratorol->getContrato());
            if($contratorol->getNombreRol()!=null)
                $sheet->setCellValue("D$i",$contratorol->getNombreRol());
            if($contratorol->getInstitucionAcreedora()!=null)
                $sheet->setCellValue("D$i",$contratorol->getInstitucionAcreedora());
            $sheet->setCellValue("D$i",$contratorol->getAbogado()->getId());
            $i++;
        }

        $sheet->setTitle($titulo);
 
        // Create your Office 2007 Excel (XLSX Format)
        $writer = new Csv($spreadSheet);
        $writer->setDelimiter('|');
        $writer->setEnclosure('');
            
        // Create a Temporary file in the system
        
        $temp_file = tempnam(sys_get_temp_dir(), $fileName);
    
        // Create the excel file in the tmp directory of the system
        $writer->save($temp_file);
    
        // Return the excel file as an attachment
        return $this->file($temp_file, $fileName, ResponseHeaderBag::DISPOSITION_ATTACHMENT);    
    }

//ContratoAnexo Hans
/**
     * @Route("/contratoanexo", name="export_gerencia_contratoanexo", methods={"GET","POST"})
     */
    public function contratoanexo(ContratoAnexoRepository $ContratoAnexoRepository): Response
    {
        $fileName="contrato_anexo.csv";
        $titulo = "ContratoAnexo TBL";
        
        $query=$ContratoAnexoRepository->findAll();
        $spreadSheet=new Spreadsheet();

        /* @var $sheet \PhpOffice\PhpSpreadsheet\Writer\Xlsx\Worksheet */
        $sheet = $spreadSheet->getActiveSheet();
        $sheet->setCellValue('A1', 'id');
        $sheet->setCellValue('B1', 'contrato_id');
        $sheet->setCellValue('C1', 'fecha_creacion');
        $sheet->setCellValue('D1', 'pdf');
        $sheet->setCellValue('E1', 'is_desiste');
       
        $sheet = $spreadSheet->getActiveSheet();
        $i=2;
        foreach($query as $contratoanexo){

            $sheet->setCellValue("A$i",$contratoanexo->getId());
            $sheet->setCellValue("B$i",$contratoanexo->getContrato()->getId());
            $sheet->setCellValue("C$i",$contratoanexo->getFechaCreacion());
            $sheet->setCellValue("D$i",$contratoanexo->getPdf());
            $sheet->setCellValue("D$i",$contratoanexo->getIsDesiste());
            $i++;
        }

        $sheet->setTitle($titulo);
 
        // Create your Office 2007 Excel (XLSX Format)
        $writer = new Csv($spreadSheet);
        $writer->setDelimiter('|');
        $writer->setEnclosure('');
            
        // Create a Temporary file in the system
        
        $temp_file = tempnam(sys_get_temp_dir(), $fileName);
    
        // Create the excel file in the tmp directory of the system
        $writer->save($temp_file);
    
        // Return the excel file as an attachment
        return $this->file($temp_file, $fileName, ResponseHeaderBag::DISPOSITION_ATTACHMENT);    
    }    

//Usuario Hans
/**
     * @Route("/usuario", name="export_gerencia_usuario", methods={"GET","POST"})
     */
    public function usuario(UsuarioRepository $usuarioRepository): Response
    {
        $fileName="usuario.csv";
        $titulo = "Usuario TBL";
        
        $query=$usuarioRepository->findAll();
        $spreadSheet=new Spreadsheet();

        /* @var $sheet \PhpOffice\PhpSpreadsheet\Writer\Xlsx\Worksheet */
        $sheet = $spreadSheet->getActiveSheet();
        $sheet->setCellValue('A1', 'id');
        $sheet->setCellValue('B1', 'usuario_tipo_id');
        $sheet->setCellValue('C1', 'username');
        $sheet->setCellValue('D1', 'nombre');
        $sheet->setCellValue('E1', 'estado');
        $sheet->setCellValue('F1', 'fecha_activacion');
        $sheet->setCellValue('G1', 'correo');
        $sheet->setCellValue('H1', 'empresa_actual');
        $sheet->setCellValue('I1', 'categoria_id');
        $sheet->setCellValue('J1', 'status_id');
        $sheet->setCellValue('K1', 'whatsapp');
        $sheet->setCellValue('L1', 'telefono');
        $sheet->setCellValue('M1', 'rut');
        $sheet->setCellValue('N1', 'direccion');
        $sheet->setCellValue('O1', 'sexo');
       
        $sheet = $spreadSheet->getActiveSheet();
        $i=2;
        foreach($query as $usuario){

            $sheet->setCellValue("A$i",$usuario->getId());
            $sheet->setCellValue("B$i",$usuario->getUsuarioTipo()->getId());
            $sheet->setCellValue("C$i",$usuario->getUsername());
            $sheet->setCellValue("D$i",$usuario->getNombre());
            $sheet->setCellValue("E$i",$usuario->getEstado());
            $sheet->setCellValue("F$i",$usuario->getFechaActivacion());
            $sheet->setCellValue("G$i",$usuario->getCorreo());
            if($usuario->getEmpresaActual()!=null)
                $sheet->setCellValue("H$i",$usuario->getEmpresaActual());
            if($usuario->getCategoria()!=null)
                $sheet->setCellValue("I$i",$usuario->getCategoria()->getId());
            if($usuario->getStatus()!=null)
                $sheet->setCellValue("J$i",$usuario->getStatus()->getId());
            if($usuario->getWhatsapp()!=null)
                $sheet->setCellValue("K$i",$usuario->getWhatsapp());
            if($usuario->getTelefono()!=null)
                $sheet->setCellValue("L$i",$usuario->getTelefono());
            if($usuario->getRut()!=null)
                $sheet->setCellValue("M$i",$usuario->getRut());
            if($usuario->getDireccion()!=null)
                $sheet->setCellValue("N$i",$usuario->getDireccion());
            if($usuario->getSexo()!=null)    
                $sheet->setCellValue("O$i",$usuario->getSexo());
            $i++;
        }

        $sheet->setTitle($titulo);
 
        // Create your Office 2007 Excel (XLSX Format)
        $writer = new Csv($spreadSheet);
        $writer->setDelimiter('|');
        $writer->setEnclosure('');
            
        // Create a Temporary file in the system
        
        $temp_file = tempnam(sys_get_temp_dir(), $fileName);
    
        // Create the excel file in the tmp directory of the system
        $writer->save($temp_file);
    
        // Return the excel file as an attachment
        return $this->file($temp_file, $fileName, ResponseHeaderBag::DISPOSITION_ATTACHMENT);    
    }

}
