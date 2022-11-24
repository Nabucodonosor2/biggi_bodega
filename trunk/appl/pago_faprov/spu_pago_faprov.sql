-------------------- spu_pago_faprov ---------------------------------
alter PROCEDURE [dbo].[spu_pago_faprov]
			(@ve_operacion					varchar(20)
			,@ve_cod_pago_faprov			numeric
			,@ve_cod_usuario				numeric		= NULL
			,@ve_cod_empresa				numeric		= NULL
			,@ve_cod_tipo_pago_faprov		numeric		= NULL
			,@ve_cod_cuenta_corriente		numeric		= NULL
			,@ve_nro_documento				numeric		= NULL
			,@ve_fecha_documento			varchar(10)	= NULL
			,@ve_monto_documento			T_PRECIO	= NULL
			,@ve_paguese_a					varchar(100)= NULL
			,@ve_cod_estado_pago_faprov		numeric		= NULL
			,@ve_cod_usuario_anula			numeric		= NULL
			,@ve_motivo_anula				varchar(100)= NULL
			,@ve_es_nominativo				varchar(1)  = NULL
			,@ve_es_cruzado					varchar(1)  = NULL
			,@ve_cod_ncprov_s				varchar(5000) = NULL)

AS
BEGIN

		declare		@kl_cod_estado_pago_faprov_anulada numeric,
					@kl_cod_estado_pago_faprov_impresa numeric,
					@vl_cod_usuario_anula numeric

		set @kl_cod_estado_pago_faprov_anulada = 3  --- estado de la faprov = anulada
		set	@kl_cod_estado_pago_faprov_impresa = 2 

		if (@ve_operacion='UPDATE') 
			begin
				UPDATE pago_faprov		
				SET		
							cod_empresa				=	@ve_cod_empresa	
							,cod_tipo_pago_faprov	=	@ve_cod_tipo_pago_faprov
							,cod_cuenta_corriente	=	@ve_cod_cuenta_corriente
							,nro_documento			=	@ve_nro_documento	
							,fecha_documento		=	dbo.to_date(@ve_fecha_documento)
							,monto_documento		=	@ve_monto_documento
							,paguese_a				=	@ve_paguese_a
							,cod_estado_pago_faprov	=	@ve_cod_estado_pago_faprov	
							,es_nominativo			=	@ve_es_nominativo
							,es_cruzado				=	@ve_es_cruzado

				WHERE cod_pago_faprov = @ve_cod_pago_faprov
				if (@ve_cod_estado_pago_faprov = @kl_cod_estado_pago_faprov_anulada) and (@vl_cod_usuario_anula is NULL) -- estado de la faprov = anulada 
					update pago_faprov
					set fecha_anula			= getdate ()
						,motivo_anula		= @ve_motivo_anula			
						,cod_usuario_anula	= @ve_cod_usuario_anula				
					where cod_pago_faprov	= @ve_cod_pago_faprov
			end
		else if (@ve_operacion='INSERT') 
			begin		
				insert into pago_faprov
					(fecha_pago_faprov
					,fecha_documento
					,cod_usuario
					,cod_empresa
					,cod_tipo_pago_faprov
					,cod_cuenta_corriente
					,nro_documento
					,monto_documento
					,paguese_a
					,cod_estado_pago_faprov
					,es_nominativo
					,es_cruzado)
				values 
					(getdate()
					,dbo.to_date(@ve_fecha_documento)
					,@ve_cod_usuario	
					,@ve_cod_empresa	
					,@ve_cod_tipo_pago_faprov	
					,@ve_cod_cuenta_corriente
					,@ve_nro_documento
					,@ve_monto_documento
					,@ve_paguese_a
					,@ve_cod_estado_pago_faprov
					,@ve_es_nominativo
					,@ve_es_cruzado)	
			end 
		else if (@ve_operacion='DELETE_ALL') 
				begin
					delete pago_faprov_faprov
    				where cod_pago_faprov = @ve_cod_pago_faprov 
					
					delete pago_faprov
					where cod_pago_faprov = @ve_cod_pago_faprov
				end 
		else if (@ve_operacion='PRINT') 	
				begin
					update pago_faprov
					set cod_estado_pago_faprov = @kl_cod_estado_pago_faprov_impresa
					where  cod_pago_faprov = @ve_cod_pago_faprov
				end 	
		else if (@ve_operacion='NCPROV')
				begin
					DECLARE
					@vl_total_con_iva	numeric(18),
					@vc_cod_nc_prov		numeric(18)
					
					DELETE NCPROV_PAGO_FAPROV
					WHERE COD_PAGO_FAPROV = @ve_cod_pago_faprov
					
					DECLARE C_NCFAPROV CURSOR FOR
					SELECT ITEM 
					FROM dbo.f_split(@ve_cod_ncprov_s,';')
					
					OPEN C_NCFAPROV
					FETCH C_NCFAPROV INTO @vc_cod_nc_prov
					WHILE @@FETCH_STATUS = 0 BEGIN
						
						SELECT @vl_total_con_iva = TOTAL_CON_IVA 
						FROM NCPROV
						WHERE COD_NCPROV = @vc_cod_nc_prov
						
						INSERT INTO NCPROV_PAGO_FAPROV
						VALUES (@ve_cod_pago_faprov, @vc_cod_nc_prov, @vl_total_con_iva)
					
						FETCH C_NCFAPROV INTO @vc_cod_nc_prov
					END
					CLOSE C_NCFAPROV
					DEALLOCATE C_NCFAPROV	
					
				end
		else if (@ve_operacion='ASIGNA_NC') begin
			delete NCPROV_USADA
			where COD_NCPROV_PAGO_FAPROV in (select COD_NCPROV_PAGO_FAPROV from NCPROV_PAGO_FAPROV where COD_PAGO_FAPROV = @ve_cod_pago_faprov)
		
			DECLARE C_NCPROV_PAGO_FAPROV insensitive CURSOR FOR
			SELECT npf.COD_NCPROV_PAGO_FAPROV
					,npf.MONTO_ASIGNADO
			FROM NCPROV_PAGO_FAPROV npf, NCPROV n
			where npf.COD_PAGO_FAPROV = @ve_cod_pago_faprov 
			  and n.COD_NCPROV = npf.COD_NCPROV
			order by n.NRO_NCPROV
			  
			
			declare
				@vc_cod_ncprov_pago_faprov		numeric
				,@vc_monto_nc					numeric
				,@vc_cod_faprov					NUMERIC
				,@vl_por_pagar					numeric
				,@vl_monto_asig					numeric

			OPEN C_NCPROV_PAGO_FAPROV
			FETCH C_NCPROV_PAGO_FAPROV INTO @vc_cod_ncprov_pago_faprov, @vc_monto_nc
			WHILE @@FETCH_STATUS = 0 BEGIN
				DECLARE C_FAPROV insensitive CURSOR FOR
				SELECT f.COD_FAPROV
				FROM PAGO_FAPROV_FAPROV ppf, FAPROV f
				where ppf.COD_PAGO_FAPROV = @ve_cod_pago_faprov 
				  and f.COD_FAPROV = ppf.COD_FAPROV
				  and dbo.f_pago_faprov_get_por_asignar(f.COD_FAPROV) > 0
				order by f.NRO_FAPROV

				OPEN C_FAPROV
				FETCH C_FAPROV INTO @vc_cod_faprov
				WHILE @@FETCH_STATUS = 0 and @vc_monto_nc > 0 BEGIN
					set @vl_por_pagar = dbo.f_pago_faprov_get_por_asignar(@vc_cod_faprov)
					if (@vc_monto_nc > @vl_por_pagar)
						set @vl_monto_asig = @vl_por_pagar
					else
						set @vl_monto_asig = @vc_monto_nc
						
					insert into NCPROV_USADA
						(COD_NCPROV_PAGO_FAPROV  
						,COD_FAPROV              
						,MONTO_ASIGNADO          
						)
					values
						(@vc_cod_ncprov_pago_faprov
						,@vc_cod_faprov
						,@vl_monto_asig
						)

					set @vc_monto_nc = @vc_monto_nc - @vl_monto_asig
					
					FETCH C_FAPROV INTO @vc_cod_faprov
				end
				CLOSE C_FAPROV
				DEALLOCATE C_FAPROV
			
				FETCH C_NCPROV_PAGO_FAPROV INTO @vc_cod_ncprov_pago_faprov, @vc_monto_nc
			end
			CLOSE C_NCPROV_PAGO_FAPROV
			DEALLOCATE C_NCPROV_PAGO_FAPROV
		end
END
go