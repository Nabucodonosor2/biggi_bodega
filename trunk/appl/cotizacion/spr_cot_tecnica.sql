--	Este procedimiento permite retornar los valores necesarios para la cotización técnica
ALTER PROCEDURE [dbo].[spr_cot_tecnica](@ve_cod_cotizacion numeric, @ve_tipo_tecnica varchar(20))
AS
BEGIN
	declare @TEMPO TABLE 	 
		   (cod_cotizacion numeric (10,0) not null,
			cod_item_cotizacion numeric not null,
			nom_empresa varchar(100) not null,			
			rut numeric not null,
			dig_verif varchar(1)not null,
			fecha_impreso varchar(50) null,
			fecha_cotizacion varchar(50) null,
			direccion varchar(100)null,
			comuna varchar(100)null,
			ciudad varchar(100)null,
			telefono varchar(100) null,
			fax varchar(100) null,
			referencia varchar(100) not null,
			nom_persona varchar(100) null,
			mail_persona varchar(100) null,
			fono_persona varchar(100) null,
			item varchar(10) null,
			nom_producto varchar(100) null,
			cod_producto varchar (30) null,
			cantidad numeric null,
			nom_usuario varchar(100) null,
			usa_electricidad varchar(1) null,
			kw numeric(10,2) null,
			total_kw numeric(10,2) null,
			voltaje numeric null,
			fases varchar(1) null,
			ciclos numeric null,
			usa_desague varchar(1) null,
			diametro_desague varchar(10) null,
			fria varchar(5) null,
			caliente varchar(5) null,
			cont_fria numeric null,
			cont_caliente numeric null,
			caudal numeric null,
			presion_agua numeric null,
			diametro_caneria varchar(10) null,
			usa_ventilacion varchar(1) null,
			volumen numeric null,
			total_vol numeric null,
			caida_presion numeric null,
			usa_vapor varchar(1) null,
			consumo_vapor numeric null,
			total_kv numeric null,
			presion_vapor numeric null,
			usa_gas varchar(1) null,
			potencia numeric(18, 2) null,
			total_gas numeric(18, 2) null,
			rut_empresa varchar(100)not null,
			sitio_web_empresa varchar(100)not null,
			nom_empresa_emisor varchar(100)not null,
			dir_empresa varchar(100) not null,
			tel_empresa varchar(100) not null,
			fax_empresa varchar(100) not null,
			mail_empresa varchar(100) not null,
			ciudad_empresa varchar(100) not null,
			pais_empresa varchar(100) null,
			giro_empresa varchar(100) not null,
			banco varchar(100) not null,
			cta_cte varchar(100) not null,
			email varchar(100) null,
			mail_usuario varchar(100) null,
			fono_usuario varchar(100) null,
			cel_usuario varchar(100) null)
	
		
insert into @TEMPO 
select 		C.COD_COTIZACION,
			IC.COD_ITEM_COTIZACION,					
			E.NOM_EMPRESA,
			E.RUT,
			E.DIG_VERIF,
			dbo.f_format_date(getdate(), 3) FECHA_IMPRESO,
			dbo.f_format_date(C.FECHA_COTIZACION, 3) FECHA_COTIZACION,				
			dbo.f_get_direccion('SUCURSAL', C.COD_SUCURSAL_FACTURA, '[DIRECCION]') DIRECCION,
			dbo.f_get_direccion('SUCURSAL', C.COD_SUCURSAL_FACTURA, '[NOM_COMUNA]') COMUNA,
			dbo.f_get_direccion('SUCURSAL', C.COD_SUCURSAL_FACTURA, '[NOM_CIUDAD]') CIUDAD,
			SF.TELEFONO,
			SF.FAX,
			C.REFERENCIA,
			P.NOM_PERSONA,
			P.EMAIL,
			P.TELEFONO,
			IC.ITEM,
			IC.NOM_PRODUCTO,
			IC.COD_PRODUCTO,
			IC.CANTIDAD,
			U. NOM_USUARIO,								
			PR.USA_ELECTRICIDAD,	 
			PR.CONSUMO_ELECTRICIDAD KW,
			IC.CANTIDAD*PR.CONSUMO_ELECTRICIDAD TOTAL_KW,
			PR.VOLTAJE,
				case PR.NRO_FASES
					when 'M' then '1'
				else case PR.NRO_FASES
					when 'T' then '3'
					end
				end FASES,
			PR.FRECUENCIA CICLOS,	
			PR.USA_DESAGUE,		
			PR.DIAMETRO_DESAGUE,		
				case PR.USA_AGUA_FRIA	
					when 'S'then '[ x ]'
				else ''
				end FRIA,
				case PR.USA_AGUA_CALIENTE
					when 'S' then '[ x ]'
				else ''
				end CALIENTE,
				case PR.USA_AGUA_FRIA
					when 'S' then 1
				else 0
				end CONT_FRIA,
				case PR.USA_AGUA_CALIENTE
					when 'S' then 1
				else 0
				end CONT_CALIENTE,
			PR.CAUDAL,
			PR.PRESION_AGUA,
			PR.DIAMETRO_CANERIA,   
			PR.USA_VENTILACION,		
			PR.VOLUMEN,
			IC.CANTIDAD * PR.VOLUMEN TOTAL_VOL,
			PR.CAIDA_PRESION,		
			PR.USA_VAPOR,			
			PR.CONSUMO_VAPOR,
			IC.CANTIDAD * PR.CONSUMO_VAPOR TOTAL_KV,
			PR.PRESION_VAPOR,		
			PR.USA_GAS,									
			PR.POTENCIA,
			IC.CANTIDAD * PR.POTENCIA TOTAL_GAS,  
			(SELECT VALOR FROM PARAMETRO WHERE COD_PARAMETRO = 20) RUT_EMPRESA,		
			(SELECT valor FROM parametro WHERE COD_PARAMETRO = 25) SITIO_WEB_EMPRESA, 
			(SELECT VALOR FROM PARAMETRO WHERE COD_PARAMETRO = 6) NOM_EMPRESA_EMISOR,		
			(SELECT valor FROM parametro WHERE COD_PARAMETRO = 10) DIR_EMPRESA, 
			(SELECT valor FROM parametro WHERE COD_PARAMETRO = 11) TEL_EMPRESA, 
			(SELECT valor FROM parametro WHERE COD_PARAMETRO = 12) FAX_EMPRESA,
			(SELECT valor FROM parametro WHERE COD_PARAMETRO = 13) MAIL_EMPRESA, 
			(SELECT valor FROM parametro WHERE COD_PARAMETRO = 14) CIUDAD_EMPRESA, 
			(SELECT valor FROM parametro WHERE COD_PARAMETRO = 15) PAIS_EMPRESA,
			(SELECT valor FROM parametro WHERE COD_PARAMETRO = 21) GIRO_EMPRESA, 
			(SELECT valor FROM parametro WHERE COD_PARAMETRO = 61) BANCO,
			(SELECT valor FROM parametro WHERE COD_PARAMETRO = 62) CTA_CTE,
			 P.EMAIL,
			 U.MAIL MAIL_USUARIO,
			 U.TELEFONO FONO_USUARIO,
			 U.CELULAR CEL_USUARIO					
	FROM COTIZACION C, EMPRESA E, PERSONA P,
		ITEM_COTIZACION IC, USUARIO U, PRODUCTO PR,
		SUCURSAL SF, SUCURSAL SD
	WHERE C.COD_COTIZACION = @ve_cod_cotizacion AND 
		E.COD_EMPRESA = C.COD_EMPRESA AND
		P.COD_PERSONA = C.COD_PERSONA AND
		IC.COD_COTIZACION = C.COD_COTIZACION AND
		U.COD_USUARIO = C.COD_USUARIO_VENDEDOR1 AND
		SF.COD_SUCURSAL = C.COD_SUCURSAL_FACTURA AND						
		SD.COD_SUCURSAL = C.COD_SUCURSAL_DESPACHO AND
		PR.COD_PRODUCTO = IC.COD_PRODUCTO	AND
		IC.COD_PRODUCTO <> 'T' 
		order by PR.COD_PRODUCTO asc

	if (@ve_tipo_tecnica='ELECTRICIDAD')
		delete @TEMPO where USA_ELECTRICIDAD = 'N'
	else if (@ve_tipo_tecnica='GAS')
		delete @TEMPO where USA_GAS = 'N'
	else if (@ve_tipo_tecnica='DESAGUE')
		delete @TEMPO where USA_DESAGUE = 'N'
	else if (@ve_tipo_tecnica='VENTILACION')
		delete @TEMPO where USA_VENTILACION = 'N'
	else if (@ve_tipo_tecnica='AGUA')
		delete @TEMPO where cont_fria = 0 and cont_caliente = 0
	else if (@ve_tipo_tecnica='VAPOR')
		delete @TEMPO where USA_VAPOR = 'N'

	declare C_TEMPO cursor for
	SELECT COD_PRODUCTO, COD_ITEM_COTIZACION
	from    @TEMPO

	declare @cod_producto varchar(30),
			@cod_producto_ant varchar(30),
			@cod_item_cotizacion numeric,
			@cod_item_cotizacion_ant numeric

	set @cod_producto_ant = ''
	set @cod_item_cotizacion_ant = 0
	OPEN C_TEMPO
	FETCH C_TEMPO INTO @cod_producto, @cod_item_cotizacion
	WHILE @@FETCH_STATUS = 0
	BEGIN
		if @cod_producto= 'T' begin	
			if (@cod_producto = @cod_producto_ant) begin
					update @TEMPO 
					set cod_item_cotizacion = -1
					where cod_item_cotizacion = @cod_item_cotizacion_ant
			end	
		end
				
		set @cod_producto_ant = @cod_producto
		set @cod_item_cotizacion_ant = @cod_item_cotizacion
		FETCH C_TEMPO INTO @cod_producto, @cod_item_cotizacion
	END
	CLOSE C_TEMPO
	DEALLOCATE C_TEMPO

	delete @TEMPO where cod_item_cotizacion = -1

	if (@ve_tipo_tecnica='ELECTRICIDAD')
		SELECT COD_COTIZACION,
					COD_ITEM_COTIZACION,
					NOM_EMPRESA,			
					RUT,
					DIG_VERIF,
					FECHA_IMPRESO,
					FECHA_COTIZACION,
					DIRECCION,
					COMUNA,
					CIUDAD,
					TELEFONO,
					FAX,
					REFERENCIA,
					NOM_PERSONA,
					MAIL_PERSONA,
					FONO_PERSONA,
					ITEM,
					NOM_PRODUCTO,
					COD_PRODUCTO,
					CANTIDAD,
					NOM_USUARIO,
					USA_ELECTRICIDAD,
					KW,
					TOTAL_KW,
					VOLTAJE,
					FASES,
					CICLOS,
					RUT_EMPRESA,		
					SITIO_WEB_EMPRESA, 
					NOM_EMPRESA_EMISOR,		
					DIR_EMPRESA,  
					TEL_EMPRESA,
					GIRO_EMPRESA,
					BANCO,
					CTA_CTE,					
					FAX_EMPRESA,
					MAIL_EMPRESA, 
					CIUDAD_EMPRESA, 
					PAIS_EMPRESA,
					EMAIL,
					NOM_USUARIO,
					MAIL_USUARIO,
					FONO_USUARIO,
					CEL_USUARIO
		from @TEMPO 
				order by COD_PRODUCTO asc

else if (@ve_tipo_tecnica='GAS')
		SELECT COD_COTIZACION,
					COD_ITEM_COTIZACION,
					NOM_EMPRESA,			
					RUT,
					DIG_VERIF,
					FECHA_IMPRESO,
					FECHA_COTIZACION,
					DIRECCION,
					COMUNA,
					CIUDAD,
					TELEFONO,
					FAX,
					REFERENCIA,
					NOM_PERSONA,
					MAIL_PERSONA,
					FONO_PERSONA,
					ITEM,
					NOM_PRODUCTO,
					COD_PRODUCTO,
					CANTIDAD,
					NOM_USUARIO,
				    USA_GAS,
				   	POTENCIA,
					TOTAL_GAS,
					RUT_EMPRESA,		
					SITIO_WEB_EMPRESA, 
					NOM_EMPRESA_EMISOR,		
					DIR_EMPRESA, 
					GIRO_EMPRESA,
					BANCO,
					CTA_CTE,
					TEL_EMPRESA, 
					FAX_EMPRESA,
					MAIL_EMPRESA, 
					CIUDAD_EMPRESA,
					PAIS_EMPRESA,
					EMAIL,
					NOM_USUARIO,
					MAIL_USUARIO,
					FONO_USUARIO,
					CEL_USUARIO
			from @TEMPO 
		order by COD_PRODUCTO asc


	else if (@ve_tipo_tecnica='VENTILACION')
		SELECT COD_COTIZACION,
					COD_ITEM_COTIZACION,
					NOM_EMPRESA,			
					RUT,
					DIG_VERIF,
					FECHA_IMPRESO,
					FECHA_COTIZACION,
					DIRECCION,
					COMUNA,
					CIUDAD,
					TELEFONO,
					FAX,
					REFERENCIA,
					NOM_PERSONA,
					MAIL_PERSONA,
					FONO_PERSONA,
					ITEM,
					NOM_PRODUCTO,
					COD_PRODUCTO,
					CANTIDAD,
					NOM_USUARIO,
				    USA_VENTILACION, 
					VOLUMEN,
				    TOTAL_VOL,
				    CAIDA_PRESION,
					RUT_EMPRESA,		
					SITIO_WEB_EMPRESA, 
					NOM_EMPRESA_EMISOR,		
					DIR_EMPRESA,  
					TEL_EMPRESA,
					GIRO_EMPRESA,
					BANCO,
					CTA_CTE,					
					FAX_EMPRESA,
					MAIL_EMPRESA,
					CIUDAD_EMPRESA,
					PAIS_EMPRESA,
					EMAIL,
					NOM_USUARIO,
					MAIL_USUARIO,
					FONO_USUARIO,
					CEL_USUARIO
				from @TEMPO 
		order by COD_PRODUCTO asc

	else if (@ve_tipo_tecnica='DESAGUE')
		SELECT COD_COTIZACION,
					COD_ITEM_COTIZACION,
					NOM_EMPRESA,			
					RUT,
					DIG_VERIF,
					FECHA_IMPRESO,
					FECHA_COTIZACION,
					DIRECCION,
					COMUNA,
					CIUDAD,
					TELEFONO,
					FAX,
					REFERENCIA,
					NOM_PERSONA,
					MAIL_PERSONA,
					FONO_PERSONA,
					ITEM,
					NOM_PRODUCTO,
					COD_PRODUCTO,
					CANTIDAD,
					NOM_USUARIO,
					USA_DESAGUE, 
					DIAMETRO_DESAGUE,
					RUT_EMPRESA,		
					SITIO_WEB_EMPRESA, 
					NOM_EMPRESA_EMISOR,		
					DIR_EMPRESA,  
					TEL_EMPRESA,
					GIRO_EMPRESA,
					BANCO,
					CTA_CTE,
					FAX_EMPRESA,
					MAIL_EMPRESA, 
					CIUDAD_EMPRESA,
					PAIS_EMPRESA,
					EMAIL,
					NOM_USUARIO,
					MAIL_USUARIO,
					FONO_USUARIO,
					CEL_USUARIO
				from @TEMPO 
		order by COD_PRODUCTO asc

else if (@ve_tipo_tecnica='AGUA')
		SELECT  COD_COTIZACION,
					COD_ITEM_COTIZACION,
					NOM_EMPRESA,			
					RUT,
					DIG_VERIF,
					FECHA_IMPRESO,
					FECHA_COTIZACION,
					DIRECCION,
					COMUNA,
					CIUDAD,
					TELEFONO,
					FAX,
					REFERENCIA,
					NOM_PERSONA,
					MAIL_PERSONA,
					FONO_PERSONA,
					ITEM,
					NOM_PRODUCTO,
					COD_PRODUCTO,
					CANTIDAD,
					NOM_USUARIO,
					FRIA,
					CALIENTE,
					CONT_FRIA,
					CONT_CALIENTE,
					CAUDAL,
					PRESION_AGUA,
					DIAMETRO_CANERIA,
					RUT_EMPRESA,		
					SITIO_WEB_EMPRESA, 
					NOM_EMPRESA_EMISOR,		
					DIR_EMPRESA,  
					TEL_EMPRESA,
					GIRO_EMPRESA,
					BANCO,
					CTA_CTE,
					FAX_EMPRESA,
					MAIL_EMPRESA, 
					CIUDAD_EMPRESA, 
					PAIS_EMPRESA,
					EMAIL,
					NOM_USUARIO,
					MAIL_USUARIO,
					FONO_USUARIO,
					CEL_USUARIO
				from @TEMPO 
		order by COD_PRODUCTO asc

else if (@ve_tipo_tecnica='VAPOR')
		SELECT  COD_COTIZACION,
					COD_ITEM_COTIZACION,
					NOM_EMPRESA,			
					RUT,
					DIG_VERIF,
					FECHA_IMPRESO,
					FECHA_COTIZACION,
					DIRECCION,
					COMUNA,
					CIUDAD,
					TELEFONO,
					FAX,
					REFERENCIA,
					NOM_PERSONA,
					MAIL_PERSONA,
					FONO_PERSONA,
					ITEM,
					NOM_PRODUCTO,
					COD_PRODUCTO,
					CANTIDAD,
					NOM_USUARIO,
					USA_VAPOR,			
					CONSUMO_VAPOR,
					TOTAL_KV,
					PRESION_VAPOR,	
					RUT_EMPRESA,		
					SITIO_WEB_EMPRESA, 
					NOM_EMPRESA_EMISOR,		
					DIR_EMPRESA,  
					TEL_EMPRESA,
					GIRO_EMPRESA,
					BANCO,
					CTA_CTE,
					FAX_EMPRESA,
					MAIL_EMPRESA, 
					CIUDAD_EMPRESA,
					PAIS_EMPRESA,
					EMAIL,	
					NOM_USUARIO,
					MAIL_USUARIO,
					FONO_USUARIO,
					CEL_USUARIO
			from @TEMPO 
		order by COD_PRODUCTO asc
END

