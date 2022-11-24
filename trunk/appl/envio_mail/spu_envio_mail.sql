ALTER PROCEDURE [dbo].[spu_envio_mail](@ve_operacion				varchar(20)
								,@ve_cod_envio_mail			numeric(10) = null
								,@ve_cod_estado_envio_mail	numeric(10) = null
								,@ve_fecha_envio			datetime	= null    
							    ,@ve_mail_from				text		= null
							    ,@ve_mail_from_name			text		= null
							    ,@ve_mail_cc				text		= null
							    ,@ve_mail_cc_name			text		= null
							    ,@ve_mail_bcc				text		= null
							    ,@ve_mail_bcc_name			text		= null
							    ,@ve_mail_to				text		= null
							    ,@ve_mail_to_name			text		= null
							    ,@ve_mail_subject			text		= null
							    ,@ve_mail_body				text		= null
								,@ve_mail_altbody			text		= null
								,@ve_tipo_doc				varchar(100)= null
								,@ve_cod_doc				numeric(10) = null
								,@ve_xml_dte				text		 = null
								,@ve_usuario_dte			varchar(100)= null)
AS
declare @vl_cod_llamado NUMERIC

BEGIN
	if (@ve_operacion='INSERT')BEGIN
		INSERT INTO ENVIO_MAIL (FECHA_REGISTRO
							   ,COD_ESTADO_ENVIO_MAIL
							   ,FECHA_ENVIO
							   ,MAIL_FROM
							   ,MAIL_FROM_NAME
							   ,MAIL_CC
							   ,MAIL_CC_NAME
							   ,MAIL_BCC
							   ,MAIL_BCC_NAME
							   ,MAIL_TO
							   ,MAIL_TO_NAME
							   ,MAIL_SUBJECT
							   ,MAIL_BODY
							   ,MAIL_ALTBODY
							   ,TIPO_DOC
							   ,COD_DOC)
					VALUES	   (getdate()
							   ,isnull(@ve_cod_estado_envio_mail, 1)
							   ,@ve_fecha_envio
							   ,@ve_mail_from
							   ,@ve_mail_from_name
							   ,@ve_mail_cc
							   ,@ve_mail_cc_name
							   ,@ve_mail_bcc
							   ,@ve_mail_bcc_name
							   ,@ve_mail_to
							   ,@ve_mail_to_name
							   ,@ve_mail_subject
							   ,@ve_mail_body
							   ,@ve_mail_altbody
							   ,isnull(@ve_tipo_doc, 'LLAMADO')
							   ,@ve_cod_doc)
			
		set @vl_cod_llamado = @ve_cod_doc
		exec spu_envio_mail 'DERIVADA_SOL_COT',@vl_cod_llamado
		
	END						   
	else if (@ve_operacion='ENVIANDOSE')BEGIN
		update ENVIO_MAIL
		set COD_ESTADO_ENVIO_MAIL = 2	--Enviandose
		where COD_ENVIO_MAIL = @ve_cod_envio_mail
	END			
	else if (@ve_operacion='ENVIANDO')BEGIN
		update ENVIO_MAIL
		set COD_ESTADO_ENVIO_MAIL = 3	--Enviado
		where COD_ENVIO_MAIL = @ve_cod_envio_mail
	END	
	else if (@ve_operacion='DERIVADA_SOL_COT')BEGIN
		
		declare @vl_cod_solicitud_cotizacion NUMERIC
				,@vl_cod_cotizacion NUMERIC
				,@vl_cod_llamado_conversa numeric
				,@K_DERIVADA_SOL_COT NUMERIC
				
		SET @K_DERIVADA_SOL_COT = 3;
			
		select @vl_cod_solicitud_cotizacion = S.COD_SOLICITUD_COTIZACION
				,@vl_cod_cotizacion = CO.COD_COTIZACION
				,@vl_cod_llamado_conversa = LL.COD_LLAMADO_CONVERSA
		from SOLICITUD_COTIZACION S 
			LEFT OUTER JOIN COTIZACION CO ON S.COD_SOLICITUD_COTIZACION = CO.COD_SOLICITUD_COTIZACION
			LEFT OUTER JOIN LLAMADO_CONVERSA LL ON S.COD_LLAMADO = LL.COD_LLAMADO
		where S.COD_LLAMADO = @ve_cod_envio_mail --@vl_cod_llamado
		
		IF ((@vl_cod_solicitud_cotizacion is not null) AND (@vl_cod_cotizacion is NULL)AND(@vl_cod_llamado_conversa is not null))
		BEGIN
			update SOLICITUD_COTIZACION
			set COD_ESTADO_SOLICITUD_COTIZACION = @K_DERIVADA_SOL_COT	--Derivada
			where COD_SOLICITUD_COTIZACION = @vl_cod_solicitud_cotizacion
		END	
	END
	else if (@ve_operacion='DERIVADA_USU_RESP')BEGIN
		DECLARE
			@ve_cod_destinatario			numeric,
			@ve_cod_usuario_resp			numeric
		
		--se reutiliza @ve_cod_envio_mail y @ve_cod_estado_envio_mail
		set @vl_cod_solicitud_cotizacion =	@ve_cod_envio_mail
		set @ve_cod_usuario_resp		 =	@ve_cod_estado_envio_mail
		
		if(@ve_cod_usuario_resp IS NOT NULL)BEGIN
			UPDATE SOLICITUD_COTIZACION
			SET COD_USUARIO_VENDEDOR1_RESP = @ve_cod_usuario_resp
			WHERE COD_SOLICITUD_COTIZACION = @vl_cod_solicitud_cotizacion
		END
	END
	else if (@ve_operacion='ACUSE_DTE')BEGIN
		INSERT INTO ENVIO_MAIL (FECHA_REGISTRO
							   ,COD_ESTADO_ENVIO_MAIL
							   ,FECHA_ENVIO
							   ,MAIL_FROM
							   ,MAIL_FROM_NAME
							   ,MAIL_CC
							   ,MAIL_CC_NAME
							   ,MAIL_BCC
							   ,MAIL_BCC_NAME
							   ,MAIL_TO
							   ,MAIL_TO_NAME
							   ,MAIL_SUBJECT
							   ,MAIL_BODY
							   ,MAIL_ALTBODY
							   ,TIPO_DOC
							   ,COD_DOC
							   ,XML_DTE
							   ,USUARIO_DTE)
					VALUES	   (getdate()
							   ,isnull(@ve_cod_estado_envio_mail, 1)
							   ,@ve_fecha_envio
							   ,@ve_mail_from
							   ,@ve_mail_from_name
							   ,@ve_mail_cc
							   ,@ve_mail_cc_name
							   ,@ve_mail_bcc
							   ,@ve_mail_bcc_name
							   ,@ve_mail_to
							   ,@ve_mail_to_name
							   ,@ve_mail_subject
							   ,@ve_mail_body
							   ,@ve_mail_altbody
							   ,isnull(@ve_tipo_doc, 'LLAMADO')
							   ,@ve_cod_doc
							   ,@ve_xml_dte
							   ,@ve_usuario_dte)
			
		
	END						   
END