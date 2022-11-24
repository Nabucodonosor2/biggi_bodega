-------------------- spu_ncprov ---------------------------------
ALTER PROCEDURE [dbo].[spu_ncprov]
			(@ve_operacion					varchar(20)
			,@ve_cod_ncprov					numeric
			,@ve_cod_usuario				numeric		= NULL
			,@ve_cod_empresa				numeric		= NULL
			,@ve_cod_estado_ncprov			numeric		= NULL
			,@ve_nro_ncprov					numeric		= NULL
			,@ve_fecha_ncprov				varchar(10)	= NULL
			,@ve_total_neto					T_PRECIO	= NULL
			,@ve_monto_iva					T_PRECIO	= NULL
			,@ve_total_con_iva				T_PRECIO	= NULL
			,@ve_cod_usuario_anula			numeric		= NULL
			,@ve_motivo_anula				varchar(100)= NULL
			,@ve_cod_cuenta_compra			numeric		= NULL
			,@ve_cod_tipo_ncprov			numeric		= NULL)

AS
BEGIN
		declare	@kl_cod_estado_ncprov_anulada numeric,
				@vl_cod_usuario_anula numeric	

		set @kl_cod_estado_ncprov_anulada = 4  --- estado de la ncprov = anulada	

		if (@ve_operacion='UPDATE') 
			begin
				UPDATE ncprov		
				SET		
							cod_empresa			=	@ve_cod_empresa	
							,cod_estado_ncprov	=	@ve_cod_estado_ncprov	
							,nro_ncprov			=	@ve_nro_ncprov	
							,fecha_ncprov		=	dbo.to_date(@ve_fecha_ncprov)
							,total_neto			=	@ve_total_neto	
							,monto_iva			=	@ve_monto_iva		
							,total_con_iva		=	@ve_total_con_iva
							,cod_cuenta_compra	=	@ve_cod_cuenta_compra
							,cod_tipo_ncprov	=	@ve_cod_tipo_ncprov

				WHERE cod_ncprov = @ve_cod_ncprov

				if (@ve_cod_estado_ncprov = @kl_cod_estado_ncprov_anulada) and (@vl_cod_usuario_anula is NULL) -- estado de la faprov = anulada 
					update ncprov
					set fecha_anula			= getdate ()
						,motivo_anula		= @ve_motivo_anula			
						,cod_usuario_anula	= @ve_cod_usuario_anula				
					where cod_ncprov  = @ve_cod_ncprov
			end
		else if (@ve_operacion='INSERT') 
			begin
				insert into ncprov
					(fecha_registro
					,fecha_ncprov
					,cod_usuario
					,cod_empresa
					,cod_estado_ncprov
					,nro_ncprov
					,total_neto
					,monto_iva
					,total_con_iva
					,cod_cuenta_compra
					,cod_tipo_ncprov)
				values 
					(getdate()
					,dbo.to_date(@ve_fecha_ncprov)
					,@ve_cod_usuario	
					,@ve_cod_empresa	
					,@ve_cod_estado_ncprov
					,@ve_nro_ncprov
					,@ve_total_neto		
					,@ve_monto_iva		
					,@ve_total_con_iva
					,@ve_cod_cuenta_compra
					,@ve_cod_tipo_ncprov)
			end 
		else if (@ve_operacion='DELETE_ALL') 
				begin
					delete ncprov_faprov
    				where cod_ncprov = @ve_cod_ncprov 
					
					delete ncprov
					where cod_ncprov = @ve_cod_ncprov
				end 
END
go

