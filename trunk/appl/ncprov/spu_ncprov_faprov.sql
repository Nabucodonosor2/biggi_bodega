-------------------- spu_ncprov_faprov ---------------------------------
CREATE PROCEDURE [dbo].[spu_ncprov_faprov]
			(@ve_operacion					varchar(20)
			,@ve_cod_ncprov_faprov			numeric
			,@ve_cod_ncprov					numeric
			,@ve_cod_faprov					numeric = null
			,@ve_monto_asignado				T_PRECIO = null)

AS
BEGIN
		if (@ve_operacion='UPDATE') 
			begin
				UPDATE ncprov_faprov
				SET		cod_ncprov		= @ve_cod_ncprov							
					   ,cod_faprov		= @ve_cod_faprov
					   ,monto_asignado	= @ve_monto_asignado	

				
				WHERE cod_ncprov_faprov = @ve_cod_ncprov_faprov 
						
			end
		else if (@ve_operacion='INSERT') 
			begin
				insert into ncprov_faprov
					(cod_ncprov
					,cod_faprov
					,monto_asignado)
				values 
					(@ve_cod_ncprov
					,@ve_cod_faprov
					,@ve_monto_asignado)
			end 
		else if (@ve_operacion='DELETE_ALL') 
			begin
				delete ncprov_faprov
    			where cod_ncprov = @ve_cod_ncprov 
			end
END
go
