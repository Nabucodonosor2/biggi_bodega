CREATE PROCEDURE [dbo].[spx_carga_oc]
AS
BEGIN  
	--variables de AUX_ORDEN_COMPRA
	declare	@identity				numeric
			,@count					numeric
			,@count_nv				numeric
			,@count_solicita		numeric
			,@count_usuario			numeric
			,@nro_oc				numeric
			,@fecha_oc				varchar(10)
			,@rut					numeric
			,@dig_verif				varchar(1)
			,@solicitante			varchar(10)
			,@numero_nota_venta		numeric
			,@sub_total				numeric
			,@dscto1				numeric(12,2)
			,@dscto2				numeric(12,2)
			,@total_dscto1			numeric(12,2)
			,@total_dscto2			numeric(12,2)
			,@total_neto			numeric
			,@total_iva				numeric(12,2)
			,@iva					numeric(5,2)
			,@total_con_iva			numeric(12,2)
			,@obs					varchar(100)
			,@referencia			varchar(100)
			,@cod_cuenta_corriente	numeric
			,@estado				varchar(15)
			,@emisor				varchar(20)

			--variables de ORDEN_COMPRA
			,@cod_usuario			varchar(4)
			,@cod_usuario_solicita	varchar(4)
			,@cod_moneda			numeric
			,@cod_estado_oc			numeric
			,@cod_nota_venta		numeric
			,@cod_empresa			numeric
			,@cod_sucursal			numeric
			,@cod_persona			numeric
			,@usuario_dscto1		numeric
			,@usuario_dscto2		numeric
			,@fecha_anula			datetime
			,@motivo_anula			varchar(100)
			,@cod_usuario_anula		numeric

	declare c_aux_oc cursor for 
	SELECT	NUMERO_OC
			,FECHA_OC
			,convert(numeric, substring(RUT_PROVEEDOR, 1, len(RUT_PROVEEDOR) - 1)) RUT --rut
			,substring(RUT_PROVEEDOR, len(RUT_PROVEEDOR), 1) DIG --dig_verif
			,SOLICITANTE
			,NUMERO_NOTA_VENTA
			,SUB_TOTAL
			,DESC1
			,DESC2
			,TOTAL_DESC1
			,TOTAL_DESC2
			,TOTAL_NETO
			,TOTAL_IVA
			,IVA
			,TOTAL_CON_IVA
			,OBS
			,REFERENCIA
			,COD_CUENTA_CORRIENTE
			,ESTADO
			,EMISOR
	FROM AUX_ORDEN_COMPRA
	WHERE NUMERO_OC < 129992
	AND RUT_PROVEEDOR <> '19' --hay 94 registros con estado anulado: con rut 19, numero_nota_venta = 0, por lo tanto no hay como obtener el dato del cliente
	AND (TIPO_OC is null OR TIPO_OC <> 'GF') --no se cargan los GF porque son de gasto fijo
	AND NUMERO_OC NOT IN (95030, 95457, 95852, 95823, 94267) --HAY 5 REGISTROS CON IVA  -10 EN AUX_ORDEN_COMPRA. TABLA ORDEN_COMPRA NO PERMITE POR REGLA INGRESAR DESCUENTOS NEGATIVOS
	ORDER BY NUMERO_OC asc

	SET IDENTITY_INSERT ORDEN_COMPRA ON

	open c_aux_oc 
	fetch c_aux_oc into @nro_oc, @fecha_oc, @rut, @dig_verif, @solicitante, @numero_nota_venta, @sub_total
						, @dscto1, @dscto2, @total_dscto1, @total_dscto2, @total_neto, @total_iva, @iva
						, @total_con_iva, @obs, @referencia, @cod_cuenta_corriente, @estado, @emisor
	WHILE @@FETCH_STATUS = 0 
	BEGIN
		SELECT @count = COUNT (*) FROM ORDEN_COMPRA WHERE COD_ORDEN_COMPRA = @nro_oc	
		IF(@count = 0)
		BEGIN
			-- ALGUNOS DATOS POR ERROR DE 4D VIENEN MENORES QUE '0' A ESTOSO SE LES DEJARA COMO NULOS IGUAL A LOS QUE TIENEN CERO
			IF (@numero_nota_venta = -50119)
				SET @numero_nota_venta = NULL

			
			set @cod_empresa = null
			set @cod_sucursal = null
			set @cod_persona = null
			set @fecha_anula = null
			set @motivo_anula = null
			set @cod_usuario_anula = null	

			-- obtiene el COD_NOTA_VENTA
			IF (@numero_nota_venta = 0)
			BEGIN
				SELECT TOP 1 @cod_empresa = COD_EMPRESA
				FROM EMPRESA 
				WHERE RUT = @rut
				
				SELECT TOP 1 @cod_sucursal = cod_sucursal 
				FROM SUCURSAL
				WHERE cod_empresa = @cod_empresa
			
				SELECT TOP 1 @cod_persona = cod_persona
				FROM PERSONA 
				WHERE cod_sucursal = @cod_sucursal 
				
				SET @numero_nota_venta = NULL
			END
			ELSE
			BEGIN				
				--los datos de la empresa los obtiene por la NV de la oc
				-- en 4D no hay integridad de datos
				SELECT @count_nv = count(*) FROM nota_venta WHERE cod_nota_venta = @numero_nota_venta
				IF(@count_nv = 0)--no existe la NV en el nuevo sistema
				BEGIN
					SELECT TOP 1 @cod_empresa = COD_EMPRESA
					FROM EMPRESA
					WHERE RUT = @rut
					
					SELECT TOP 1 @cod_sucursal = cod_sucursal 
					FROM SUCURSAL
					WHERE cod_empresa = @cod_empresa
				
					SELECT TOP 1 @cod_persona = cod_persona
					FROM PERSONA 
					WHERE cod_sucursal = @cod_sucursal 
					
					SET @numero_nota_venta = NULL
				END
				ELSE 
				BEGIN
					SELECT	@cod_empresa	= NV.COD_EMPRESA
					FROM NOTA_VENTA NV ,EMPRESA E
					WHERE NV.COD_NOTA_VENTA = @numero_nota_venta
					AND	NV.COD_EMPRESA = E.COD_EMPRESA
					
					SELECT TOP 1 @cod_sucursal = cod_sucursal 
					FROM SUCURSAL
					WHERE cod_empresa = @cod_empresa
				
					SELECT TOP 1 @cod_persona = cod_persona
					FROM PERSONA 
					WHERE cod_sucursal = @cod_sucursal 
					
				END
			END		

			SELECT @count_solicita = COUNT (*) FROM USUARIO WHERE INI_USUARIO = @solicitante
			IF(@count_solicita = 0)
			BEGIN
				SET @cod_usuario_solicita =(CASE @solicitante
					WHEN 'CUI'	THEN (SELECT COD_USUARIO FROM USUARIO WHERE INI_USUARIO = 'CU')
					WHEN 'CUY'	THEN (SELECT COD_USUARIO FROM USUARIO WHERE INI_USUARIO = 'CU')
					WHEN 'D'	THEN (SELECT COD_USUARIO FROM USUARIO WHERE INI_USUARIO = 'ADM')
					WHEN 'D2'	THEN (SELECT COD_USUARIO FROM USUARIO WHERE INI_USUARIO = 'ADM')
					WHEN 'HC'	THEN (SELECT COD_USUARIO FROM USUARIO WHERE INI_USUARIO = 'ADM')
					WHEN 'KIS'	THEN (SELECT COD_USUARIO FROM USUARIO WHERE INI_USUARIO = 'IKS')
					WHEN 'NJ'	THEN (SELECT COD_USUARIO FROM USUARIO WHERE INI_USUARIO = 'IKS')
					WHEN 'P`L'	THEN (SELECT COD_USUARIO FROM USUARIO WHERE INI_USUARIO = 'PL')
					ELSE (SELECT COD_USUARIO FROM USUARIO WHERE INI_USUARIO = 'ADM')
				END)
			END
			ELSE
			BEGIN
				SELECT @cod_usuario_solicita = COD_USUARIO 
				FROM USUARIO
				WHERE INI_USUARIO = @solicitante
			END
	
			SELECT @count_usuario = COUNT (*) FROM USUARIO WHERE NOM_USUARIO = @emisor
			IF(@count_usuario = 0)
			BEGIN
				SET @cod_usuario =(CASE @emisor
					WHEN 'NORMA JOFRE R.'	THEN (SELECT COD_USUARIO FROM USUARIO WHERE COD_USUARIO = 14)
					WHEN 'INDUSTRIAL KITCHEN SERVICE S.A.'	THEN (SELECT COD_USUARIO FROM USUARIO WHERE COD_USUARIO = 14)
					WHEN 'JORGE GARNICA'	THEN (SELECT COD_USUARIO FROM USUARIO WHERE COD_USUARIO = 20)
					--'CAROLINA ADAROS U' NO EXISTE COMO USUARIO. PERO ADMINISTRADOR REGISTRA  COMO EMISOR DE LA OC
					WHEN 'CAROLINA ADAROS U'	THEN (SELECT COD_USUARIO FROM USUARIO WHERE COD_USUARIO = 1)
					WHEN 'DISEÐADOR'	THEN (SELECT COD_USUARIO FROM USUARIO WHERE COD_USUARIO = 1)
					--'HERNAN CARO (TODOINOX)' NO EXISTE COMO USUARIO. PERO ADMINISTRADOR REGISTRA  COMO EMISOR DE LA OC
					WHEN 'HERNAN CARO (TODOINOX)'	THEN (SELECT COD_USUARIO FROM USUARIO WHERE COD_USUARIO = 1)
					ELSE (SELECT COD_USUARIO FROM USUARIO WHERE COD_USUARIO = 1)
				END)
			END
			ELSE
			BEGIN
				SELECT @cod_usuario = COD_USUARIO 
				FROM USUARIO 
				WHERE NOM_USUARIO = @emisor
			END			
			
			--HAY VARIOS REGISTROS CON  DATO DE COD_CUENTA_CORRIENTE  = '0'  CONVERSADO CON IVAN  SE LE VA ASIGNAR COMERCIAL COD = '1'
			IF (@cod_cuenta_corriente = 0)
			BEGIN
				SET @cod_cuenta_corriente = 1
			END

			--HAY VARIOS REGISTROS DE REFERENCIA CON NULL  PERO  EN OC  NO SE PERMITEN NULOS
			IF (@referencia IS NULL)
			BEGIN
				SET @referencia = ''
			END
			
		
		-- ingresando los datos de aux_orden_compra a orden_compra
		INSERT INTO ORDEN_COMPRA
		(COD_ORDEN_COMPRA
		,FECHA_ORDEN_COMPRA
		,COD_USUARIO
		,COD_USUARIO_SOLICITA
		,COD_MONEDA
		,COD_ESTADO_ORDEN_COMPRA
		,COD_NOTA_VENTA
		,COD_CUENTA_CORRIENTE
		,REFERENCIA
		,COD_EMPRESA
		,COD_SUCURSAL
		,COD_PERSONA
		,SUBTOTAL
		,PORC_DSCTO1
		,MONTO_DSCTO1
		,INGRESO_USUARIO_DSCTO1
		,PORC_DSCTO2
		,MONTO_DSCTO2
		,INGRESO_USUARIO_DSCTO2
		,TOTAL_NETO
		,PORC_IVA
		,MONTO_IVA
		,TOTAL_CON_IVA
		,OBS
		,FECHA_ANULA
		,MOTIVO_ANULA
		,COD_USUARIO_ANULA)
		VALUES
		(@nro_oc
		,dbo.to_date(@fecha_oc)
		,@cod_usuario
		,@cod_usuario_solicita
		,1				--@cod_moneda TODOS SON PESOS
		,1				--@cod_estado_oc ESTADO DEJADO COMO EMITIDO CONVERSADO CON  'IS'
		,@numero_nota_venta 
		,@cod_cuenta_corriente
		,@referencia
		,@cod_empresa
		,@cod_sucursal
		,@cod_persona
		,@sub_total
		,@dscto1
		,@total_dscto1
		,'M'			--INGRESO_USUARIO_DSCTO1 referente de FA
		,@dscto2
		,@total_dscto2
		,'M'			--INGRESO_USUARIO_DSCTO2 referente de FA
		,@total_neto
		,@iva
		,@total_iva
		,@total_con_iva
		,@obs
		,@fecha_anula	--FECHA_ANULA	NO TIENE DATOS EN 4D
		,@motivo_anula	--MOTIVO_ANULA	NO TIENE DATOS EN 4D
		,@cod_usuario_anula	--COD_USUARIO_ANULA	NO TIENE DATOS EN 4D
		)

		END
		fetch c_aux_oc into @nro_oc, @fecha_oc, @rut, @dig_verif, @solicitante, @numero_nota_venta, @sub_total
							, @dscto1, @dscto2, @total_dscto1, @total_dscto2, @total_neto, @total_iva, @iva
							, @total_con_iva, @obs, @referencia, @cod_cuenta_corriente, @estado, @emisor
	END
	CLOSE c_aux_oc
	DEALLOCATE c_aux_oc
	
	SET IDENTITY_INSERT ORDEN_COMPRA OFF
END
go