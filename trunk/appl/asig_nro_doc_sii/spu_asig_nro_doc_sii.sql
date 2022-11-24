---------------------spu_asig_nro_doc_sii----------------------
CREATE PROCEDURE [dbo].[spu_asig_nro_doc_sii]
			(@ve_operacion					varchar(20)
			,@ve_cod_asig_nro_doc_sii		numeric
			,@ve_fecha_asig					varchar(20)=NULL
			,@ve_cod_usuario				numeric =NULL
			,@ve_cod_tipo_doc_sii			varchar(20)=NULL
			,@ve_nro_inicio					numeric =NULL
			,@ve_nro_termino				numeric =NULL
			,@ve_cod_usuario_receptor		numeric =NULL
			,@ve_fecha_devol				varchar(20)=NULL
			,@ve_nro_inicio_devol			numeric =NULL
			,@ve_nro_termino_devol			numeric =NULL) 
AS
BEGIN
		if (@ve_operacion='UPDATE') 
			begin
				update asig_nro_doc_sii		
				set	cod_usuario			= @ve_cod_usuario				
					,cod_tipo_doc_sii		= @ve_cod_tipo_doc_sii			
					,nro_inicio				= @ve_nro_inicio				
					,nro_termino			= @ve_nro_termino				
					,cod_usuario_receptor	= @ve_cod_usuario_receptor		
					,fecha_devol			= case @ve_fecha_devol when 'get_date' then getdate() end
					,nro_inicio_devol		= @ve_nro_inicio_devol			
					,nro_termino_devol		= @ve_nro_termino_devol			
				where cod_asig_nro_doc_sii	= @ve_cod_asig_nro_doc_sii
			end
		else if (@ve_operacion='INSERT') 
			begin
				insert into asig_nro_doc_sii
					(fecha_asig
					,cod_usuario
					,cod_tipo_doc_sii
					,nro_inicio
					,nro_termino
					,cod_usuario_receptor
					,fecha_devol
					,nro_inicio_devol
					,nro_termino_devol)
				values 
					(getdate()
					,@ve_cod_usuario	
					,@ve_cod_tipo_doc_sii	
					,@ve_nro_inicio	
					,@ve_nro_termino
					,@ve_cod_usuario_receptor
					,@ve_fecha_devol
					,@ve_nro_inicio_devol
					,@ve_nro_termino_devol)
			end 
END
go